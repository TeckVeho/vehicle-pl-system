<?php

namespace Repository;

use App\Models\GoogleSpreadsheet;
use App\Models\GoogleSpreadsheetSheet;
use App\Models\Vehicle;
use App\Models\VehicleITPS3Data;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\SpreadsheetProperties;
use Google\Service\Sheets\SheetProperties;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleSpreadsheetService
{
    protected Client $client;
    protected Drive $driveService;
    protected Sheets $sheetsService;

    // Folder structure constants
    const ROOT_FOLDER_NAME = 'CL_VehicleOperationSchedule';
    const SPREADSHEET_NAME = '2025 事業部車両稼働表';

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('google.sheets.application_name'));
        $this->client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE_FILE,
            Drive::DRIVE
        ]);

        $this->client->setClientId(config('google.oauth2.client_id'));
        $this->client->setClientSecret(config('google.oauth2.client_secret'));
        $this->client->setRedirectUri(config('google.oauth2.redirect_uri'));
        $this->client->setScopes(config('google.oauth2.scopes'));

        $this->driveService = new Drive($this->client);
        $this->sheetsService = new Sheets($this->client);
    }

    /**
     * Set access token cho OAuth 2.0
     */
    public function setAccessToken($accessToken)
    {
        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            }
        }

        $this->driveService = new Drive($this->client);
        $this->sheetsService = new Sheets($this->client);
    }

    /**
     * Load access token từ file JSON
     */
    public function loadAccessTokenFromFile($filePath = null)
    {
        if (!$filePath) {
            $filePath = storage_path('app/google_oauth_token.json');
        }

        if (!file_exists($filePath)) {
            throw new \Exception("Token file not found: {$filePath}");
        }

        $tokenJson = file_get_contents($filePath);
        $token = json_decode($tokenJson, true);

        if (!$token) {
            throw new \Exception("Invalid token JSON in file: {$filePath}");
        }

        $this->setAccessToken($token);
        return $token;
    }

    /**
     * Kiểm tra authentication status
     */
    public function isAuthenticated(): bool
    {
        try {
            $token = $this->client->getAccessToken();
            if (!$token) {
                return false;
            }

            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    return true;
                }
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function getCurrentUser(): ?array
    {
        try {
            if (!$this->isAuthenticated()) {
                return null;
            }

            // Test kết nối bằng cách lấy thông tin Drive
            $about = $this->driveService->about->get(['fields' => 'user']);

            return [
                'email' => $about->getUser()->getEmailAddress(),
                'name' => $about->getUser()->getDisplayName(),
                'permission_id' => $about->getUser()->getPermissionId()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tạo cấu trúc thư mục chính
     */
    public function createRootFolder(): string
    {
        $folderName = $this->getRootFolderNameWithEnvironment();

        $existingFolder = $this->findFolderByName($folderName);
        if ($existingFolder) {
            return $existingFolder['id'];
        }

        $sharedFolder = $this->findSharedFolderByName($folderName);
        if ($sharedFolder) {
            return $sharedFolder['id'];
        }

        $folderMetadata = new Drive\DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        $folder = $this->driveService->files->create($folderMetadata, [
            'fields' => 'id'
        ]);

        return $folder->id;
    }

    /**
     * Tạo hoặc lấy spreadsheet chính cho tất cả departments
     */
    public function createMainSpreadsheet(): string
    {
        $rootFolderId = $this->createRootFolder();
        $spreadsheetName = $this->getSpreadsheetNameWithEnvironment();

        $existingSpreadsheet = $this->findSpreadsheetInFolder($spreadsheetName, $rootFolderId);
        if ($existingSpreadsheet) {
            return $existingSpreadsheet['id'];
        }

        $spreadsheet = new Spreadsheet([
            'properties' => new SpreadsheetProperties([
                'title' => $spreadsheetName
            ])
        ]);

        try {
            $createdSpreadsheet = $this->sheetsService->spreadsheets->create($spreadsheet);
            $this->moveFileToFolder($createdSpreadsheet->getSpreadsheetId(), $rootFolderId);

            // Lưu vào database
            $this->saveSpreadsheetToDatabase($createdSpreadsheet->getSpreadsheetId(), $spreadsheetName, $rootFolderId);

            return $createdSpreadsheet->getSpreadsheetId();

        } catch (\Exception $e) {
            throw new \Exception("Không thể tạo spreadsheet: " . $e->getMessage());
        }
    }

    /**
     * Tạo sheet cho department với format vehicle operation schedule
     */
    public function createDepartmentSheet(string $spreadsheetId, string $departmentName): int
    {
        $sheetName = $departmentName;

        $existingSheet = $this->findSheetByName($spreadsheetId, $sheetName);
        if ($existingSheet) {
            return $existingSheet['properties']['sheetId'];
        }

        // Kiểm tra xem có sheet mặc định "Sheet1" rỗng không
        $defaultSheet = $this->findDefaultEmptySheet($spreadsheetId);

        if ($defaultSheet) {
            // Rename sheet mặc định thành department name
            $requests = [
                new Sheets\Request([
                    'updateSheetProperties' => new Sheets\UpdateSheetPropertiesRequest([
                        'properties' => new SheetProperties([
                            'sheetId' => $defaultSheet['sheetId'],
                            'title' => $sheetName
                        ]),
                        'fields' => 'title'
                    ])
                ])
            ];

            $batchUpdateRequest = new Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                $batchUpdateRequest
            );

            $newSheetId = $defaultSheet['sheetId'];
        } else {
            // Tạo sheet mới
            $requests = [
                new Sheets\Request([
                    'addSheet' => new Sheets\AddSheetRequest([
                        'properties' => new SheetProperties([
                            'title' => $sheetName
                        ])
                    ])
                ])
            ];

            $batchUpdateRequest = new Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ]);

            $response = $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                $batchUpdateRequest
            );

            $newSheetId = $response->getReplies()[0]->getAddSheet()->getProperties()->getSheetId();
        }

        // Setup format cho sheet
        $this->setupVehicleOperationSheetFormat($spreadsheetId, $sheetName);

        // Lưu vào database
        $this->saveSheetToDatabase($spreadsheetId, $newSheetId, $departmentName);

        return $newSheetId;
    }

    /**
     * Setup format cho vehicle operation schedule sheet
     */
    private function setupVehicleOperationSheetFormat(string $spreadsheetId, string $sheetName): void
    {
        // Resize sheet để đảm bảo có đủ cột
        $this->resizeSheet($spreadsheetId, $sheetName);

        // Tạo header theo format yêu cầu
        $headers = $this->createVehicleOperationHeaders();

        // Update data
        $this->updateSheetData($spreadsheetId, $sheetName, $headers);

        // Apply formatting
        $this->applyVehicleOperationFormatting($spreadsheetId, $sheetName);

        // Force refresh để đảm bảo tất cả cột được hiển thị
        $this->forceRefreshSheet($spreadsheetId, $sheetName);
    }

    /**
     * Tạo headers cho vehicle operation schedule theo mẫu thực tế
     * Mỗi giờ được merge 2 cột (E:F, G:H, I:J, ...)
     */
    private function createVehicleOperationHeaders(): array
    {
        $headers = [];

        // Row 1: Headers chính
        $row1 = ['', '東京稼働表', '', '']; // A1, B1, C1, D1 (B1:D2 sẽ được merge, text phải ở B1)

        // Thêm 24 giờ (E1 đến AZ1) - Mỗi giờ chiếm 2 cột
        // E:F = 0:00, G:H = 1:00, I:J = 2:00, ...
        for ($hour = 0; $hour <= 23; $hour++) {
            $row1[] = sprintf('%d:00', $hour); // Cột đầu của merge
            $row1[] = ''; // Cột thứ 2 của merge (empty)
        }

        // Thêm cột thống kê
        $row1[] = '稼働時間'; // BA1
        $row1[] = '稼働率';   // BB1

        // Thêm empty cho các cột còn lại
        while (count($row1) < 60) {
            $row1[] = '';
        }

        $headers[] = $row1;

        // Row 2: Sub-headers
        $row2 = ['', '', '', '']; // A2, B2, C2, D2

        // "コース名+運賃" từ E2 đến AZ2 (merge tất cả 24 giờ * 2 cột = 48 cột)
        $row2[] = 'コース名+運賃'; // E2

        // Empty cho các cột còn lại trong merge (F2 đến AZ2)
        for ($i = 1; $i < 48; $i++) {
            $row2[] = '';
        }

        // Empty cho các cột thống kê và còn lại
        while (count($row2) < 60) {
            $row2[] = '';
        }

        $headers[] = $row2;

        return $headers;
    }

    /**
     * Apply formatting cho vehicle operation sheet
     */
    private function applyVehicleOperationFormatting(string $spreadsheetId, string $sheetName): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);

        $requests = [
            // Merge B1:D2 cho "東京稼働表"
            [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 2,
                        'startColumnIndex' => 1, // B
                        'endColumnIndex' => 4     // D
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ],
            // Merge E2:AZ2 cho "コース名+運賃"
            [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 1,
                        'endRowIndex' => 2,
                        'startColumnIndex' => 4, // E
                        'endColumnIndex' => 52    // AZ (4 + 48)
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ],
            // Style cho headers (row 1 và row 2) - chỉ đến cột BB
            [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 2,
                        'startColumnIndex' => 0,
                        'endColumnIndex' => 54 // A đến BB (54 cột)
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => [
                                'red' => 0.9,
                                'green' => 0.9,
                                'blue' => 0.9
                            ],
                            'textFormat' => [
                                'bold' => true,
                                'fontSize' => 10
                            ],
                            'horizontalAlignment' => 'CENTER',
                            'verticalAlignment' => 'MIDDLE',
                            'borders' => [
                                'top' => ['style' => 'SOLID', 'color' => ['red' => 0, 'green' => 0, 'blue' => 0]],
                                'bottom' => ['style' => 'SOLID', 'color' => ['red' => 0, 'green' => 0, 'blue' => 0]],
                                'left' => ['style' => 'SOLID', 'color' => ['red' => 0, 'green' => 0, 'blue' => 0]],
                                'right' => ['style' => 'SOLID', 'color' => ['red' => 0, 'green' => 0, 'blue' => 0]]
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,verticalAlignment,borders)'
                ]
            ]
        ];

        // Thêm merge cells cho mỗi giờ (E:F, G:H, I:J, ...)
        for ($hour = 0; $hour <= 23; $hour++) {
            $startCol = 4 + ($hour * 2); // E=4, G=6, I=8, ...
            $endCol = $startCol + 2;     // F=6, H=8, J=10, ...

            $requests[] = [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                        'startColumnIndex' => $startCol,
                        'endColumnIndex' => $endCol
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ];
        }

        $this->sheetsService->spreadsheets->batchUpdate(
            $spreadsheetId,
            new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ])
        );
    }

    /**
     * Apply merge cells cho dữ liệu vehicle
     */
    private function applyVehicleDataMerges(string $spreadsheetId, string $sheetName, int $vehicleCount): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
        $requests = [];

        // Merge cells cho mỗi vehicle (mỗi vehicle chiếm 2 rows)
        for ($i = 0; $i < $vehicleCount; $i++) {
            $startRow = 2 + ($i * 2); // Row 3, 5, 7, ... (0-indexed: 2, 4, 6, ...)
            $endRow = $startRow + 2;   // Row 5, 7, 9, ...

            // Merge A (STT)
            $requests[] = [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $endRow,
                        'startColumnIndex' => 0, // A
                        'endColumnIndex' => 1     // A
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ];

            // Merge B (Biển số xe)
            $requests[] = [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $endRow,
                        'startColumnIndex' => 1, // B
                        'endColumnIndex' => 2     // B
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ];

            // Merge C:D (Loại xe) ở row thứ 2 của vehicle
            $requests[] = [
                'mergeCells' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow + 1, // Row 4, 6, 8, ...
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 2, // C
                        'endColumnIndex' => 4     // D
                    ],
                    'mergeType' => 'MERGE_ALL'
                ]
            ];
        }

        if (!empty($requests)) {
            $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests
                ])
            );
        }
    }

    /**
     * Apply borders cho dữ liệu vehicle
     */
    private function applyVehicleDataBorders(string $spreadsheetId, string $sheetName, int $vehicleCount): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
        $requests = [];

        $border = [
            'style' => 'SOLID',
            'color' => ['red' => 0, 'green' => 0, 'blue' => 0]
        ];

        // Apply borders cho từng vehicle
        for ($i = 0; $i < $vehicleCount; $i++) {
            $startRow = 2 + ($i * 2); // Row 3, 5, 7, ... (0-indexed: 2, 4, 6, ...)

            // Row 1 của vehicle (A-BB): viền đầy đủ
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 1,
                        'startColumnIndex' => 0,  // A
                        'endColumnIndex' => 54     // BB
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'borders' => [
                                'top' => $border,
                                'bottom' => $border,
                                'left' => $border,
                                'right' => $border
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat.borders'
                ]
            ];

            // Row 2 của vehicle: B-D viền đầy đủ
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow + 1,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 1,  // B
                        'endColumnIndex' => 4      // D
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'borders' => [
                                'top' => $border,
                                'bottom' => $border,
                                'left' => $border,
                                'right' => $border
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat.borders'
                ]
            ];

            // Row 2 của vehicle: E-AZ CHỈ có bottom border
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow + 1,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 4,   // E
                        'endColumnIndex' => 52      // AZ
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'borders' => [
                                'bottom' => $border
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat.borders'
                ]
            ];

            // Row 2 của vehicle: BA-BB viền đầy đủ
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow + 1,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 52,  // BA
                        'endColumnIndex' => 54      // BB
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'borders' => [
                                'top' => $border,
                                'bottom' => $border,
                                'left' => $border,
                                'right' => $border
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat.borders'
                ]
            ];
        }

        if (!empty($requests)) {
            $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests
                ])
            );
        }
    }

    /**
     * Apply dropdown list cho cột loại xe (C:D row 2 của mỗi vehicle)
     */
    private function applyVehicleTypeDropdown(string $spreadsheetId, string $sheetName, int $vehicleCount): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
        $requests = [];

        $vehicleTypes = [
            '',
            '2t ドライ',
            '2t チルド',
            '3t チルド',
            '3t チルドG',
            '3t 冷凍G',
            '4t チルド',
            '4t チルドG',
            '4t 冷凍G',
            '増t ゲート',
            '大型ドライ',
            '大型チルド'
        ];

        for ($i = 0; $i < $vehicleCount; $i++) {
            $startRow = 2 + ($i * 2);
            $vehicleTypeRow = $startRow + 1;

            $requests[] = [
                'setDataValidation' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $vehicleTypeRow,
                        'endRowIndex' => $vehicleTypeRow + 1,
                        'startColumnIndex' => 2,
                        'endColumnIndex' => 4
                    ],
                    'rule' => [
                        'condition' => [
                            'type' => 'ONE_OF_LIST',
                            'values' => array_map(function ($type) {
                                return ['userEnteredValue' => $type];
                            }, $vehicleTypes)
                        ],
                        'showCustomUi' => true,
                        'strict' => false
                    ]
                ]
            ];
        }

        if (!empty($requests)) {
            $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests
                ])
            );
        }
    }

    /**
     * Apply text alignment cho dữ liệu vehicle
     * A3:A4(merge) ~ Ax:Ax+1(merge): text căn giữa
     * B3:B4(merge) ~ Bx:Bx+1(merge): text căn giữa và cho phép xuống dòng
     * C3 ~ Cx+2 : text căn phải
     * D3 ~ Dx+2 : text căn phải
     * C4:D4(merge) ~ Cx+2:Dx+2(merge) căn giữa
     */
    private function applyVehicleDataAlignment(string $spreadsheetId, string $sheetName, int $vehicleCount): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
        $requests = [];

        for ($i = 0; $i < $vehicleCount; $i++) {
            $startRow = 2 + ($i * 2);

            // A3:A4 (STT) - căn giữa
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 0,
                        'endColumnIndex' => 1
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'CENTER',
                            'verticalAlignment' => 'MIDDLE'
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment)'
                ]
            ];

            // B3:B4 (Biển số) - căn giữa và wrap text
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 1,
                        'endColumnIndex' => 2
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'CENTER',
                            'verticalAlignment' => 'MIDDLE',
                            'wrapStrategy' => 'WRAP'
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment,wrapStrategy)'
                ]
            ];

            // C3 (Năm) - căn phải
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 1,
                        'startColumnIndex' => 2,
                        'endColumnIndex' => 3
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'RIGHT',
                            'verticalAlignment' => 'MIDDLE'
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment)'
                ]
            ];

            // D3 (Tháng) - căn phải
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 1,
                        'startColumnIndex' => 3,
                        'endColumnIndex' => 4
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'RIGHT',
                            'verticalAlignment' => 'MIDDLE'
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment)'
                ]
            ];

            // C4:D4 (Loại xe) - căn giữa
            $requests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow + 1,
                        'endRowIndex' => $startRow + 2,
                        'startColumnIndex' => 2,
                        'endColumnIndex' => 4
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'horizontalAlignment' => 'CENTER',
                            'verticalAlignment' => 'MIDDLE'
                        ]
                    ],
                    'fields' => 'userEnteredFormat(horizontalAlignment,verticalAlignment)'
                ]
            ];
        }

        if (!empty($requests)) {
            $this->sheetsService->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests
                ])
            );
        }
    }

    /**
     * Cập nhật dữ liệu vehicle operation cho department
     */
    public function updateVehicleOperationData(string $departmentName): array
    {
        try {
            // Lấy hoặc tạo spreadsheet chính
            $spreadsheetId = $this->getOrCreateMainSpreadsheet();

            // Lấy hoặc tạo sheet cho department
            $sheetInfo = $this->getOrCreateDepartmentSheet($spreadsheetId, $departmentName);

            // Lấy dữ liệu vehicle từ database
            $vehicleData = $this->getVehicleDataForDepartment($departmentName);

            // Format dữ liệu theo yêu cầu
            $formattedData = $this->formatVehicleOperationData($vehicleData);

            // Update dữ liệu vào sheet
            $this->updateVehicleDataToSheet($spreadsheetId, $sheetInfo['sheet_name'], $formattedData);

            // Apply merge cells cho dữ liệu vehicle
            $vehicleCount = count($vehicleData);
            $this->applyVehicleDataMerges($spreadsheetId, $sheetInfo['sheet_name'], $vehicleCount);

            // Apply borders cho dữ liệu vehicle
            $this->applyVehicleDataBorders($spreadsheetId, $sheetInfo['sheet_name'], $vehicleCount);

            // Apply dropdown list cho cột loại xe (C:D)
            $this->applyVehicleTypeDropdown($spreadsheetId, $sheetInfo['sheet_name'], $vehicleCount);

            // Apply text alignment cho dữ liệu vehicle
            $this->applyVehicleDataAlignment($spreadsheetId, $sheetInfo['sheet_name'], $vehicleCount);

            // Apply màu vàng cho thời gian hoạt động
            $this->applyOperationTimeHighlighting($spreadsheetId, $sheetInfo['sheet_name'], $formattedData);

            return [
                'success' => true,
                'message' => 'Cập nhật dữ liệu vehicle operation thành công',
                'spreadsheet_id' => $spreadsheetId,
                'sheet_name' => $sheetInfo['sheet_name'],
                'url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit#gid={$sheetInfo['sheet_id']}"
            ];

        } catch (\Exception $e) {
            Log::error('Update vehicle operation data error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi cập nhật dữ liệu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy dữ liệu vehicle cho department
     */
    private function getVehicleDataForDepartment(string $departmentName): array
    {
        // Lấy department ID
        $department = \App\Models\Department::where('name', $departmentName)->first();
        if (!$department) {
            return [];
        }

        // Lấy vehicles theo department với dữ liệu ITP S3
        $vehicles = Vehicle::where('department_id', $department->id)->get();

        return $vehicles->toArray();
    }

    /**
     * Format dữ liệu vehicle operation theo mẫu thực tế (2 rows per vehicle)
     */
    private function formatVehicleOperationData(array $vehicles): array
    {
        $formattedData = [];
        $vehicleNumber = 1;

        foreach ($vehicles as $vehicle) {
            // Tính toán thời gian hoạt động trung bình 1 tuần
            $operationTimes = $this->calculateWeeklyAverageOperationTime($vehicle);

            // Row 1: Thông tin xe chính
            $row1 = [
                $vehicleNumber, // Số thứ tự (A) - sẽ merge với A+1
                $this->getLatestNoNumberPlate($vehicle), // Biển số xe (B) - sẽ merge với B+1 - join với vehicle_no_number_plate_history
                $this->getVehicleYear($vehicle), // Năm (C)
                $this->getVehicleMonth($vehicle), // Tháng (D)
            ];

            // Dữ liệu cho 48 cells (E đến AZ) - mỗi cell = 30 phút
            // Các ô thể hiện màu vàng cho thời gian hoạt động
            for ($cellIndex = 0; $cellIndex < 48; $cellIndex++) {
                $operationData = $this->getOperationDataForCell($vehicle, $cellIndex, $operationTimes);
                $row1[] = $operationData;
            }
            // Bây giờ $row1 có: 4 (A-D) + 48 (E-AZ) = 52 cột

            // Thêm cột thống kê (BA, BB) = 2 cột
            $row1[] = ''; // BA - 稼働時間
            $row1[] = ''; // BB - 稼働率
            // Bây giờ $row1 có: 54 cột

            // Thêm 6 cột còn lại (BC-BH) để đủ 60 cột
            for ($i = 0; $i < 6; $i++) {
                $row1[] = '';
            }

            $formattedData[] = $row1;

            // Row 2: Loại xe và route info
            $row2 = [
                '', // Empty (A) - sẽ merge với A row trước
                '', // Empty (B) - sẽ merge với B row trước
                '', // Loại xe (C) - sẽ merge với D - Mặc định rỗng để user chọn từ dropdown
                '', // Empty (D) - sẽ merge với C
            ];

            // Route info cho 48 cells (E đến AZ) - TẠM THỜI ĐỂ TRỐNG
            // Sẽ implement sau khi có dữ liệu route
            for ($cellIndex = 0; $cellIndex < 48; $cellIndex++) {
                $routeInfo = $this->getRouteInfoForCell($vehicle, $cellIndex, $operationTimes);
                $row2[] = $routeInfo;
            }
            // Bây giờ $row2 có: 4 (A-D) + 48 (E-AZ) = 52 cột

            // Thêm cột thống kê (BA, BB) = 2 cột
            $row2[] = ''; // BA
            $row2[] = ''; // BB
            // Bây giờ $row2 có: 54 cột

            // Thêm 6 cột còn lại (BC-BH) để đủ 60 cột
            for ($i = 0; $i < 6; $i++) {
                $row2[] = '';
            }

            $formattedData[] = $row2;
            $vehicleNumber++;
        }

        return $formattedData;
    }

    /**
     * Tính toán thời gian hoạt động trung bình 1 tuần từ dữ liệu ITP S3
     * Logic:
     * 1. Lấy dữ liệu tuần vừa qua (Thứ 2 → Chủ nhật)
     * 2. Nhóm theo GIỜ (hour) của start_time
     * 3. Đếm số ngày xuất hiện của mỗi giờ
     * 4. CHỈ tính trung bình cho các khung giờ có tỷ lệ > 51% (xuất hiện > 3.57 ngày trong 7 ngày)
     * 5. Tính trung bình start_time và end_time cho các nhóm đủ điều kiện
     * 6. Trả về các khoảng thời gian cần tô màu vàng
     */
    private function calculateWeeklyAverageOperationTime(array $vehicle): array
    {
        $vehicleId = $vehicle['id'] ?? null;

        if (!$vehicleId) {
            return ['time_ranges' => []];
        }

        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);

        $weekData = \App\Models\VehicleITPS3Data::where('vehicle_id', $vehicleId)
            ->whereBetween('start_date_time', [$startOfLastWeek, $endOfLastWeek])
            ->get();

        if ($weekData->isEmpty()) {
            return ['time_ranges' => []];
        }

        $totalDaysInWeek = 7;
        $minOccurrencePercentage = 51;
        $minOccurrenceDays = ceil($totalDaysInWeek * $minOccurrencePercentage / 100);

        $groupedByHour = [];
        $daysByHour = [];

        foreach ($weekData as $record) {
            $startDateTime = Carbon::parse($record->start_date_time);
            $endDateTime = Carbon::parse($record->end_date_time);

            $startHour = $startDateTime->hour;
            $dateKey = $startDateTime->format('Y-m-d');

            if (!isset($groupedByHour[$startHour])) {
                $groupedByHour[$startHour] = [
                    'start_times' => [],
                    'end_times' => []
                ];
                $daysByHour[$startHour] = [];
            }

            $groupedByHour[$startHour]['start_times'][] = $startDateTime;
            $groupedByHour[$startHour]['end_times'][] = $endDateTime;
            
            if (!in_array($dateKey, $daysByHour[$startHour])) {
                $daysByHour[$startHour][] = $dateKey;
            }
        }

        $timeRanges = [];

        foreach ($groupedByHour as $hour => $times) {
            $daysCount = count($daysByHour[$hour]);
            $occurrencePercentage = ($daysCount / $totalDaysInWeek) * 100;

            if ($daysCount < $minOccurrenceDays) {
                \Log::info("Bỏ qua giờ {$hour}:00 - Chỉ xuất hiện {$daysCount}/{$totalDaysInWeek} ngày ({$occurrencePercentage}%) - Cần tối thiểu {$minOccurrenceDays} ngày (>{$minOccurrencePercentage}%)");
                continue;
            }

            \Log::info("Tính trung bình cho giờ {$hour}:00 - Xuất hiện {$daysCount}/{$totalDaysInWeek} ngày ({$occurrencePercentage}%)");

            $startTimes = $times['start_times'];
            $endTimes = $times['end_times'];

            $avgStartMinutes = 0;
            foreach ($startTimes as $startTime) {
                $avgStartMinutes += ($startTime->hour * 60) + $startTime->minute;
            }
            $avgStartMinutes = round($avgStartMinutes / count($startTimes));

            $avgEndMinutes = 0;
            foreach ($endTimes as $endTime) {
                $totalMinutes = ($endTime->hour * 60) + $endTime->minute;

                if ($totalMinutes < $avgStartMinutes) {
                    $totalMinutes += 24 * 60;
                }

                $avgEndMinutes += $totalMinutes;
            }
            $avgEndMinutes = round($avgEndMinutes / count($endTimes));

            $timeRanges[] = [
                'start_minutes' => $avgStartMinutes,
                'end_minutes' => $avgEndMinutes,
                'start_hour' => floor($avgStartMinutes / 60),
                'start_minute' => $avgStartMinutes % 60,
                'end_hour' => floor($avgEndMinutes / 60) % 24,
                'end_minute' => $avgEndMinutes % 60,
                'occurrence_days' => $daysCount,
                'occurrence_percentage' => round($occurrencePercentage, 2)
            ];
        }

        return ['time_ranges' => $timeRanges];
    }

    /**
     * Lấy dữ liệu operation cho cell cụ thể (mỗi cell = 30 phút)
     * Xác định xem cell này có nằm trong khoảng thời gian hoạt động không
     *
     * @param array $vehicle Vehicle data
     * @param int $cellIndex Cell index (0-47 tương ứng với E-AZ, mỗi cell = 30 phút)
     * @param array $operationTimes Dữ liệu thời gian hoạt động
     * @return string Trả về marker để biết có tô màu hay không
     */
    private function getOperationDataForCell(array $vehicle, int $cellIndex, array $operationTimes): string
    {
        $timeRanges = $operationTimes['time_ranges'] ?? [];

        if (empty($timeRanges)) {
            return '';
        }

        $cellStartMinutes = $cellIndex * 30;
        $cellEndMinutes = $cellStartMinutes + 30;

        foreach ($timeRanges as $range) {
            $rangeStartMinutes = $range['start_minutes'];
            $rangeEndMinutes = $range['end_minutes'];

            if ($this->isCellInTimeRange($cellStartMinutes, $cellEndMinutes, $rangeStartMinutes, $rangeEndMinutes)) {
                return 'ACTIVE';
            }
        }

        return '';
    }

    /**
     * Kiểm tra xem cell có nằm trong khoảng thời gian không
     */
    private function isCellInTimeRange(int $cellStart, int $cellEnd, int $rangeStart, int $rangeEnd): bool
    {
        if ($rangeEnd > 24 * 60) {
            if ($cellStart >= $rangeStart || $cellEnd <= ($rangeEnd % (24 * 60))) {
                return true;
            }
        }

        return $cellStart < $rangeEnd && $cellEnd > $rangeStart;
    }

    /**
     * Lấy thông tin route cho cell cụ thể
     * TẠM THỜI trả về empty - sẽ implement sau khi có dữ liệu route
     */
    private function getRouteInfoForCell(array $vehicle, int $cellIndex, array $operationTimes): string
    {
        return '';
    }

    /**
     * Lấy thông tin route cho giờ cụ thể từ dữ liệu ITP S3
     */
    private function getRouteInfoForHour(array $vehicle, int $hour, array $operationTimes): string
    {
        $itpData = $vehicle['vehicle_i_t_p_s3_data'] ?? [];

        if (empty($itpData)) {
            return '';
        }

        // Tìm dữ liệu ITP cho giờ cụ thể
        foreach ($itpData as $data) {
            $startDateTime = Carbon::parse($data['start_date_time']);
            $endDateTime = Carbon::parse($data['end_date_time']);

            $startHour = $startDateTime->hour;
            $endHour = $endDateTime->hour;

            // Kiểm tra xem giờ hiện tại có nằm trong khoảng hoạt động không
            if ($hour >= $startHour && $hour <= $endHour) {
                // Tạo thông tin route dựa trên dữ liệu thực tế
                $duration = $endDateTime->diffInMinutes($startDateTime);
                $fare = $this->calculateFareFromOperation($data);
                $tollFee = $this->calculateTollFeeFromOperation($data);

                // Tạo route info string
                $routeInfo = $this->generateRouteInfoString($data, $fare, $tollFee);
                return $routeInfo;
            }
        }

        return '';
    }

    /**
     * Lấy biển số xe mới nhất từ vehicle_no_number_plate_history
     */
    private function getLatestNoNumberPlate(array $vehicle): string
    {
        $vehicleId = $vehicle['id'] ?? null;

        if (!$vehicleId) {
            return $vehicle['vehicle_identification_number'] ?? '';
        }

        $latestHistory = \App\Models\VehicleNoNumberPlateHistory::where('vehicle_id', $vehicleId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestHistory && $latestHistory->no_number_plate) {
            return $latestHistory->no_number_plate;
        }

        return $vehicle['vehicle_identification_number'] ?? '';
    }

    /**
     * Lấy năm của vehicle từ vehicle master
     */
    private function getVehicleYear(array $vehicle): string
    {
        // Lấy năm từ first_registration hoặc vehicle_delivery_date
        if (isset($vehicle['first_registration']) && $vehicle['first_registration']) {
            $date = Carbon::parse($vehicle['first_registration']);
            return $date->year;
        } elseif (isset($vehicle['vehicle_delivery_date']) && $vehicle['vehicle_delivery_date']) {
            $date = Carbon::parse($vehicle['vehicle_delivery_date']);
            return $date->year;
        }

        return '';
    }

    /**
     * Lấy tháng của vehicle từ vehicle master
     */
    private function getVehicleMonth(array $vehicle): string
    {
        // Lấy tháng từ first_registration hoặc vehicle_delivery_date
        if (isset($vehicle['first_registration']) && $vehicle['first_registration']) {
            $date = Carbon::parse($vehicle['first_registration']);
            return $date->month;
        } elseif (isset($vehicle['vehicle_delivery_date']) && $vehicle['vehicle_delivery_date']) {
            $date = Carbon::parse($vehicle['vehicle_delivery_date']);
            return $date->month;
        }

        return '';
    }

    /**
     * Tính fare từ dữ liệu operation
     */
    private function calculateFareFromOperation(array $data): float
    {
        // TODO: Implement logic tính fare dựa trên dữ liệu ITP S3
        // Có thể dựa trên thời gian hoạt động, khoảng cách, loại xe, etc.
        $startDateTime = Carbon::parse($data['start_date_time']);
        $endDateTime = Carbon::parse($data['end_date_time']);
        $duration = $endDateTime->diffInMinutes($startDateTime);

        // Tạm thời tính fare dựa trên thời gian hoạt động (1000 yen/giờ)
        return round(($duration / 60) * 1000);
    }

    /**
     * Tính toll fee từ dữ liệu operation
     */
    private function calculateTollFeeFromOperation(array $data): float
    {
        // TODO: Implement logic tính toll fee dựa trên dữ liệu ITP S3
        // Có thể dựa trên route, khoảng cách, thời gian, etc.
        $startDateTime = Carbon::parse($data['start_date_time']);
        $endDateTime = Carbon::parse($data['end_date_time']);
        $duration = $endDateTime->diffInMinutes($startDateTime);

        // Tạm thời tính toll fee dựa trên thời gian hoạt động (100 yen/giờ)
        return round(($duration / 60) * 100);
    }

    /**
     * Tạo chuỗi thông tin route
     */
    private function generateRouteInfoString(array $data, float $fare, float $tollFee): string
    {
        $startDateTime = Carbon::parse($data['start_date_time']);
        $endDateTime = Carbon::parse($data['end_date_time']);

        // Tạo route name dựa trên thời gian và vehicle info
        $routeName = sprintf('Route_%s_%s',
            $startDateTime->format('Hi'),
            $endDateTime->format('Hi')
        );

        // Format fare và toll fee
        $fareStr = number_format($fare);
        $tollStr = number_format($tollFee);

        if ($tollFee > 0) {
            return sprintf('%s (%s + %s)', $routeName, $fareStr, $tollStr);
        } else {
            return sprintf('%s (%s)', $routeName, $fareStr);
        }
    }

    /**
     * Tìm thời gian phổ biến nhất trong mảng
     */
    private function getMostCommonTime(array $times): string
    {
        if (empty($times)) {
            return '08:00';
        }

        $timeCounts = array_count_values($times);
        $mostCommonTime = array_search(max($timeCounts), $timeCounts);

        return $mostCommonTime ?: '08:00';
    }

    /**
     * Lấy danh sách loại xe chi tiết
     */
    private function getVehicleTypes(array $vehicle): array
    {
        // Tạo danh sách loại xe chi tiết dựa trên thông tin vehicle
        $types = [];

        // Thêm các loại xe cơ bản
        if (isset($vehicle['type'])) {
            $types[] = $vehicle['type'];
        }

        // Thêm các loại xe khác dựa trên thông tin vehicle
        if (isset($vehicle['box_shape'])) {
            $types[] = $vehicle['box_shape'];
        }

        if (isset($vehicle['truck_classification'])) {
            $types[] = $vehicle['truck_classification'];
        }

        // Thêm các loại xe mặc định
        $defaultTypes = ['2t ドライ', '2t チルド', '3t チルド', '3t チルドG', '4t チルド', '大型チルド'];

        // Kết hợp và giới hạn 30 loại
        $allTypes = array_merge($types, $defaultTypes);
        $allTypes = array_unique($allTypes);
        $allTypes = array_slice($allTypes, 0, 30);

        // Pad với empty strings để đủ 30 cột
        while (count($allTypes) < 30) {
            $allTypes[] = '';
        }

        return $allTypes;
    }

    /**
     * Resize sheet để đảm bảo có đủ cột và rows
     */
    private function resizeSheet(string $spreadsheetId, string $sheetName): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);

        $requests = [
            [
                'updateSheetProperties' => [
                    'properties' => [
                        'sheetId' => $sheetId,
                        'gridProperties' => [
                            'rowCount' => 1000,
                            'columnCount' => 60 // A đến BD
                        ]
                    ],
                    'fields' => 'gridProperties(rowCount,columnCount)'
                ]
            ]
        ];

        $this->sheetsService->spreadsheets->batchUpdate(
            $spreadsheetId,
            new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ])
        );
    }

    /**
     * Force refresh sheet và set column width để giống mẫu
     */
    private function forceRefreshSheet(string $spreadsheetId, string $sheetName): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);

        $requests = [
            // Set width cho cột A (STT)
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'COLUMNS',
                        'startIndex' => 0, // A
                        'endIndex' => 1    // A
                    ],
                    'properties' => [
                        'pixelSize' => 40 // Width cho cột STT
                    ],
                    'fields' => 'pixelSize'
                ]
            ],
            // Set width cho cột B-D (東京稼働表)
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'COLUMNS',
                        'startIndex' => 1, // B
                        'endIndex' => 4    // D
                    ],
                    'properties' => [
                        'pixelSize' => 80 // Width cho cột B-D
                    ],
                    'fields' => 'pixelSize'
                ]
            ],
            // Set width cho các cột thời gian (E-AZ) - 48 cột = 24 giờ * 2
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'COLUMNS',
                        'startIndex' => 4,  // E
                        'endIndex' => 52    // AZ (4 + 48)
                    ],
                    'properties' => [
                        'pixelSize' => 25 // Width nhỏ cho các cột giờ (10-30px)
                    ],
                    'fields' => 'pixelSize'
                ]
            ],
            // Set width cho các cột thống kê (BA-BB)
            [
                'updateDimensionProperties' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'COLUMNS',
                        'startIndex' => 52, // BA
                        'endIndex' => 54    // BB
                    ],
                    'properties' => [
                        'pixelSize' => 80 // Width cho các cột thống kê
                    ],
                    'fields' => 'pixelSize'
                ]
            ]
        ];

        $this->sheetsService->spreadsheets->batchUpdate(
            $spreadsheetId,
            new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests
            ])
        );
    }

    /**
     * Update dữ liệu vehicle vào sheet
     */
    private function updateVehicleDataToSheet(string $spreadsheetId, string $sheetName, array $data): void
    {
        if (empty($data)) {
            return;
        }

        // Clear existing data (từ row 3 trở đi)
        $this->clearSheetDataFromRow($spreadsheetId, $sheetName, 3);

        // Update new data (từ row 3, cột A đến BH - 60 columns)
        $range = $sheetName . '!A3:BH' . (2 + count($data));

        $this->sheetsService->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            new \Google\Service\Sheets\ValueRange([
                'values' => $data
            ]),
            ['valueInputOption' => 'RAW']
        );
    }

    /**
     * Apply conditional formatting cho thời gian hoạt động (tô màu vàng)
     */
    /**
     * Apply màu vàng cho các cells có marker 'ACTIVE'
     * Logic:
     * 1. Reset tất cả cells E-AZ về màu trắng trước
     * 2. Loop qua từng row của vehicle data
     * 3. Nếu cell có giá trị 'ACTIVE', tô màu vàng
     * 4. Clear text 'ACTIVE' sau khi tô màu
     */
    private function applyOperationTimeHighlighting(string $spreadsheetId, string $sheetName, array $data): void
    {
        $sheetId = $this->getSheetId($spreadsheetId, $sheetName);
        $vehicleCount = count($data) / 2;
        
        $resetRequests = [];
        $highlightRequests = [];
        $clearRequests = [];

        // Bước 1: Reset tất cả cells E-AZ (cột 4-51) về màu trắng cho tất cả vehicles
        for ($i = 0; $i < $vehicleCount; $i++) {
            $startRow = 2 + ($i * 2);
            
            $resetRequests[] = [
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => $startRow,
                        'endRowIndex' => $startRow + 1,
                        'startColumnIndex' => 4,
                        'endColumnIndex' => 52
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => [
                                'red' => 1.0,
                                'green' => 1.0,
                                'blue' => 1.0
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat.backgroundColor'
                ]
            ];
        }

        // Bước 2 & 3: Tô màu vàng cho các cells ACTIVE
        foreach ($data as $rowIndex => $row) {
            if ($rowIndex % 2 !== 0) {
                continue;
            }

            $actualRowIndex = 2 + $rowIndex;

            for ($colIndex = 4; $colIndex < 52; $colIndex++) {
                if (isset($row[$colIndex]) && $row[$colIndex] === 'ACTIVE') {
                    $highlightRequests[] = [
                        'repeatCell' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => $actualRowIndex,
                                'endRowIndex' => $actualRowIndex + 1,
                                'startColumnIndex' => $colIndex,
                                'endColumnIndex' => $colIndex + 1
                            ],
                            'cell' => [
                                'userEnteredFormat' => [
                                    'backgroundColor' => [
                                        'red' => 1.0,
                                        'green' => 1.0,
                                        'blue' => 0.0
                                    ]
                                ]
                            ],
                            'fields' => 'userEnteredFormat.backgroundColor'
                        ]
                    ];

                    $clearRequests[] = [
                        'updateCells' => [
                            'range' => [
                                'sheetId' => $sheetId,
                                'startRowIndex' => $actualRowIndex,
                                'endRowIndex' => $actualRowIndex + 1,
                                'startColumnIndex' => $colIndex,
                                'endColumnIndex' => $colIndex + 1
                            ],
                            'rows' => [
                                [
                                    'values' => [
                                        [
                                            'userEnteredValue' => [
                                                'stringValue' => ''
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'fields' => 'userEnteredValue'
                        ]
                    ];
                }
            }
        }

        $batchSize = 100;

        // Execute reset requests trước
        if (!empty($resetRequests)) {
            $resetBatches = array_chunk($resetRequests, $batchSize);

            foreach ($resetBatches as $batch) {
                $this->sheetsService->spreadsheets->batchUpdate(
                    $spreadsheetId,
                    new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                        'requests' => $batch
                    ])
                );
            }
        }

        // Execute highlight requests
        if (!empty($highlightRequests)) {
            $highlightBatches = array_chunk($highlightRequests, $batchSize);

            foreach ($highlightBatches as $batch) {
                $this->sheetsService->spreadsheets->batchUpdate(
                    $spreadsheetId,
                    new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                        'requests' => $batch
                    ])
                );
            }
        }

        // Execute clear text requests
        if (!empty($clearRequests)) {
            $clearBatches = array_chunk($clearRequests, $batchSize);

            foreach ($clearBatches as $batch) {
                $this->sheetsService->spreadsheets->batchUpdate(
                    $spreadsheetId,
                    new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                        'requests' => $batch
                    ])
                );
            }
        }
    }

    /**
     * Tìm thời gian bắt đầu hoạt động trong row
     */
    private function findOperationStartTime(array $row): ?int
    {
        // Tìm cột đầu tiên có dữ liệu (từ cột 3 trở đi, sau "初年度", "月" và "初年度/月")
        for ($i = 3; $i < count($row) - 34; $i++) { // -34 vì có 4 cột thống kê + 30 cột loại xe
            if (!empty(trim($row[$i]))) {
                return $i - 3; // Trả về index giờ (0-23)
            }
        }
        return null;
    }

    /**
     * Tìm thời gian kết thúc hoạt động trong row
     */
    private function findOperationEndTime(array $row): ?int
    {
        // Tìm cột cuối cùng có dữ liệu (từ cột 3 trở đi)
        for ($i = count($row) - 35; $i >= 3; $i--) { // -35 vì có 4 cột thống kê + 30 cột loại xe
            if (!empty(trim($row[$i]))) {
                return $i - 2; // Trả về index giờ (0-23)
            }
        }
        return null;
    }

    /**
     * Lấy hoặc tạo spreadsheet chính
     */
    public function getOrCreateMainSpreadsheet(): string
    {
        try {
            $spreadsheetName = $this->getSpreadsheetNameWithEnvironment();

            // Tìm trong database trước
            $existingSpreadsheet = GoogleSpreadsheet::where('spreadsheet_name', $spreadsheetName)->first();
            if ($existingSpreadsheet) {
                return $existingSpreadsheet->spreadsheet_id;
            }

            // Tạo mới nếu không tìm thấy
            return $this->createMainSpreadsheet();

        } catch (\Exception $e) {
            throw new \Exception("Không thể tạo hoặc lấy spreadsheet chính: " . $e->getMessage());
        }
    }

    /**
     * Lấy hoặc tạo sheet cho department
     */
    public function getOrCreateDepartmentSheet(string $spreadsheetId, string $departmentName): array
    {
        $sheetId = $this->createDepartmentSheet($spreadsheetId, $departmentName);
        $sheetName = $departmentName;

        return [
            'spreadsheet_id' => $spreadsheetId,
            'sheet_id' => $sheetId,
            'sheet_name' => $sheetName,
            'range' => "{$sheetName}!A1:BD1000",
            'url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/edit#gid={$sheetId}"
        ];
    }

    /**
     * Lưu spreadsheet vào database
     */
    private function saveSpreadsheetToDatabase(string $spreadsheetId, string $spreadsheetName, string $folderId): void
    {
        GoogleSpreadsheet::updateOrCreate(
            ['spreadsheet_id' => $spreadsheetId],
            [
                'spreadsheet_name' => $spreadsheetName,
                'folder_id' => $folderId,
                'year' => Carbon::now()->year,
                'last_sync_at' => Carbon::now(),
                'sync_status' => 'completed'
            ]
        );
    }

    /**
     * Lưu sheet vào database
     */
    private function saveSheetToDatabase(string $spreadsheetId, int $sheetId, string $departmentName): void
    {
        $department = \App\Models\Department::where('name', $departmentName)->first();

        GoogleSpreadsheetSheet::updateOrCreate(
            [
                'spreadsheet_id' => $spreadsheetId,
                'sheet_id' => $sheetId
            ],
            [
                'department_id' => $department ? $department->id : null,
                'title' => $departmentName,
                'last_sync_at' => Carbon::now()
            ]
        );
    }

    /**
     * Cập nhật dữ liệu hàng tuần (chạy lúc 8h sáng chủ nhật)
     */
    public function weeklyUpdateAllDepartments(): array
    {
        $results = [];
        $departments = \App\Models\Department::all();

        foreach ($departments as $department) {
            try {
                $result = $this->updateVehicleOperationData($department->name);
                $results[$department->name] = $result;
            } catch (\Exception $e) {
                $results[$department->name] = [
                    'success' => false,
                    'message' => 'Lỗi: ' . $e->getMessage()
                ];
            }
        }

        return $results;
    }

    // Helper methods từ file cũ
    private function findFolderByName(string $name, string $parentId = null): ?array
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$name}' and trashed=false";
        if ($parentId) {
            $query .= " and '{$parentId}' in parents";
        }

        $response = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)'
        ]);

        $files = $response->getFiles();
        return count($files) > 0 ? ['id' => $files[0]->getId(), 'name' => $files[0]->getName()] : null;
    }

    private function findSpreadsheetInFolder(string $name, string $folderId): ?array
    {
        $query = "mimeType='application/vnd.google-apps.spreadsheet' and name='{$name}' and '{$folderId}' in parents and trashed=false";

        $response = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)'
        ]);

        $files = $response->getFiles();
        return count($files) > 0 ? ['id' => $files[0]->getId(), 'name' => $files[0]->getName()] : null;
    }

    private function findSheetByName(string $spreadsheetId, string $sheetName): ?array
    {
        $spreadsheet = $this->sheetsService->spreadsheets->get($spreadsheetId);

        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return [
                    'properties' => [
                        'sheetId' => $sheet->getProperties()->getSheetId(),
                        'title' => $sheet->getProperties()->getTitle()
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * Tìm sheet mặc định rỗng (Sheet1) khi tạo spreadsheet mới
     */
    private function findDefaultEmptySheet(string $spreadsheetId): ?array
    {
        $spreadsheet = $this->sheetsService->spreadsheets->get($spreadsheetId);
        $sheets = $spreadsheet->getSheets();

        if (count($sheets) === 1) {
            $sheet = $sheets[0];
            $title = $sheet->getProperties()->getTitle();

            if ($title === 'Sheet1' || preg_match('/^Sheet\d+$/i', $title)) {
                return [
                    'sheetId' => $sheet->getProperties()->getSheetId(),
                    'title' => $title
                ];
            }
        }

        return null;
    }

    private function moveFileToFolder(string $fileId, string $folderId): void
    {
        $file = $this->driveService->files->get($fileId, ['fields' => 'parents']);
        $previousParents = implode(',', $file->parents);

        $this->driveService->files->update($fileId, new Drive\DriveFile(), [
            'addParents' => $folderId,
            'removeParents' => $previousParents,
            'fields' => 'id, parents'
        ]);
    }


    private function findSharedFolderByName(string $name): ?array
    {
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$name}' and trashed=false and sharedWithMe=true";

        $response = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)'
        ]);

        $files = $response->getFiles();
        return count($files) > 0 ? ['id' => $files[0]->getId(), 'name' => $files[0]->getName()] : null;
    }

    public function updateSheetData(string $spreadsheetId, string $sheetName, array $data): void
    {
        if (empty($data)) {
            return;
        }

        $maxRows = count($data);
        $maxCols = max(array_map('count', $data));

        $endCol = $this->columnToLetter($maxCols);
        $range = $sheetName . '!A1:' . $endCol . $maxRows;

        $this->sheetsService->spreadsheets_values->update(
            $spreadsheetId,
            $range,
            new \Google\Service\Sheets\ValueRange([
                'values' => $data
            ]),
            ['valueInputOption' => 'RAW']
        );
    }

    private function clearSheetDataFromRow(string $spreadsheetId, string $sheetName, int $startRow): void
    {
        $range = "{$sheetName}!A{$startRow}:BD1000";
        $this->sheetsService->spreadsheets_values->clear(
            $spreadsheetId,
            $range,
            new \Google\Service\Sheets\ClearValuesRequest()
        );
    }

    private function clearSheetData(string $spreadsheetId, string $sheetName): void
    {
        $range = "{$sheetName}!A1:BD1000";
        $this->sheetsService->spreadsheets_values->clear(
            $spreadsheetId,
            $range,
            new \Google\Service\Sheets\ClearValuesRequest()
        );
    }

    private function getSheetId(string $spreadsheetId, string $sheetName): int
    {
        $spreadsheet = $this->sheetsService->spreadsheets->get($spreadsheetId);

        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return $sheet->getProperties()->getSheetId();
            }
        }

        throw new \Exception("Sheet not found: {$sheetName}");
    }

    private function columnToLetter(int $columnIndex): string
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = intval($columnIndex / 26);
        }
        return $letter ?: 'A';
    }

    public function getSpreadsheetNameWithEnvironment(): string
    {
        $environment = app()->environment();
        if ($environment === 'production') {
            return self::SPREADSHEET_NAME;
        }
        return self::SPREADSHEET_NAME . '_' . $environment;
    }

    public function getRootFolderNameWithEnvironment(): string
    {
        $environment = app()->environment();
        if ($environment === 'production') {
            return self::ROOT_FOLDER_NAME;
        }
        return self::ROOT_FOLDER_NAME . '_' . $environment;
    }
}
