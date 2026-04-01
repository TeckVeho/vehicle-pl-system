<?php

namespace Repository;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Script;
use Google\Service\Script\CreateProjectRequest;
use Google\Service\Script\Content;
use App\Models\GoogleScript;
use Google\Service\Sheets;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAppsScriptService
{
    protected Client $client;
    protected Drive $driveService;
    protected Script $scriptService;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('google.sheets.application_name'));
        $this->client->setScopes([
            Sheets::SPREADSHEETS,
            Drive::DRIVE_FILE,
            Drive::DRIVE,
            Script::SCRIPT_PROJECTS,
            Script::SCRIPT_DEPLOYMENTS,
            Script::SCRIPT_PROCESSES
        ]);

        // Chỉ sử dụng OAuth 2.0
        $this->client->setClientId(config('google.oauth2.client_id'));
        $this->client->setClientSecret(config('google.oauth2.client_secret'));
        $this->client->setRedirectUri(config('google.oauth2.redirect_uri'));
        $this->client->setScopes(config('google.oauth2.scopes'));

        $this->driveService = new Drive($this->client);
        $this->scriptService = new Script($this->client);
    }

    /**
     * Set access token cho OAuth 2.0
     */
    public function setAccessToken($accessToken)
    {
        $this->client->setAccessToken($accessToken);

        // Refresh token nếu cần
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            }
        }

        $this->driveService = new Drive($this->client);
        $this->scriptService = new Script($this->client);
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
     * Tạo tên script với environment và department
     */
    public function getScriptNameWithEnvironment(string $departmentName): string
    {
        $environment = app()->environment();
        // Format: "Workshift Script_{environment}-{department}"
        return "Workshift Script_$environment-$departmentName";
    }

    /**
     * Cập nhật tên script
     */
    public function updateScriptTitle(string $scriptId, string $newTitle): bool
    {
        try {
            // Cách 1: Sử dụng Drive API để đổi tên file
            $file = new \Google\Service\Drive\DriveFile();
            $file->setName($newTitle);

            $result = $this->driveService->files->update($scriptId, $file);

            Log::info("[updateScriptTitle] Đã cập nhật tên script thành công");
            return true;
        } catch (\Exception $e) {
            Log::error("[updateScriptTitle] Lỗi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo script content với code
     */
    public function createScriptContent(string $scriptId, array $files): array
    {
        try {
            $contentFiles = [];

            foreach ($files as $fileName => $fileSource) {
                $contentFiles[] = [
                    'name' => pathinfo($fileName, PATHINFO_FILENAME), // bỏ phần .js
                    'type' => strtoupper(pathinfo($fileName, PATHINFO_EXTENSION)) === 'JSON' ? 'JSON' : 'SERVER_JS',
                    'source' => $fileSource
                ];
            }

            // appsscript.json
            $contentFiles[] = [
                'name' => 'appsscript',
                'type' => 'JSON',
                'source' => json_encode([
                    'timeZone' => 'Asia/Tokyo',
                    'dependencies' => (object)[],
                    'exceptionLogging' => 'STACKDRIVER',
                    'runtimeVersion' => 'V8'
                ], JSON_PRETTY_PRINT)
            ];

            $content = new Content(['files' => $contentFiles]);

            $this->scriptService->projects->updateContent($scriptId, $content);

            Log::info("[createScriptContent] Updated content for script: {$scriptId}");

            return [
                'success' => true,
                'script_id' => $scriptId
            ];

        } catch (\Exception $e) {
            Log::error("[createScriptContent] Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo deployment cho script
     */
    public function createScriptDeployment(string $scriptId, string $versionNumber = '1'): array
    {
        try {
            $deploymentConfig = new \Google\Service\Script\DeploymentConfig([
                'versionNumber' => $versionNumber,
                'manifestFileName' => 'appsscript',
                'description' => 'Workshift Spreadsheet Webhook Deployment'
            ]);

            $createdDeployment = $this->scriptService->projects_deployments->create($scriptId, $deploymentConfig);

            Log::info("[createScriptDeployment] Created deployment: {$createdDeployment->getDeploymentId()}");

            return [
                'success' => true,
                'deployment_id' => $createdDeployment->getDeploymentId(),
                'script_id' => $scriptId,
                'version_number' => $createdDeployment->getVersionNumber()
            ];

        } catch (\Exception $e) {
            Log::error("[createScriptDeployment] Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy thông tin script project
     */
    public function getScriptProject(string $scriptId): array
    {
        try {
            $project = $this->scriptService->projects->get($scriptId);

            $result = [
                'success' => true,
                'script_id' => $project->getScriptId(),
                'title' => $project->getTitle(),
                'createTime' => $project->getCreateTime(),
                'updateTime' => $project->getUpdateTime()
            ];

            // Kiểm tra các thuộc tính container/parent
            if (method_exists($project, 'getParentId')) {
                $result['parent_id'] = $project->getParentId();
            }

            if (method_exists($project, 'getParent')) {
                $result['parent'] = $project->getParent();
            }

            // Lấy thông tin container nếu có
            if (method_exists($project, 'getContainer')) {
                $container = $project->getContainer();
                if ($container) {
                    $result['container'] = [
                        'id' => $container->getId(),
                        'type' => $container->getType(),
                        'name' => $container->getName()
                    ];
                }
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("[getScriptProject] Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate Apps Script code
     */
    public function generateAppsScriptCode(string $departmentName): array
    {
        if (App::environment() === 'production') {
            $url = 'https://ws.izumilogi.com/api/shift/update-data-ai-result';
        } elseif (App::environment() === 'staging') {
            $url = 'https://ws-stage.izumilogi.com/api/shift/update-data-ai-result';
        } elseif (App::environment() === 'dev') {
            $url = 'https://izumi-ai-shift.vw-dev.com/api/shift/update-data-ai-result';
        } else {
            $url = 'https://ca68d60f4adf.ngrok-free.app/api/shift/update-data-ai-result';
        }

        $setupJs = <<<JAVASCRIPT
function addTriggers() {
  const triggers = ScriptApp.getProjectTriggers();
  const alreadyExists = triggers.some(trigger =>
    trigger.getHandlerFunction() === 'onEditTrigger' &&
    trigger.getEventType() === ScriptApp.EventType.ON_EDIT
  );

  if (!alreadyExists) {
    ScriptApp.newTrigger("onEditTrigger")
      .forSpreadsheet(SpreadsheetApp.getActiveSpreadsheet())
      .onEdit()
      .create();
    Logger.log("✅ Trigger đã được tạo tự động khi mở file.");
  } else {
    Logger.log("ℹ️ Trigger đã tồn tại.");
  }
}
JAVASCRIPT;

        $mainJs = <<<JAVASCRIPT
function onEditTrigger(e) {
  try {
    const range = e.range;
    const sheet = range.getSheet();
    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();

    const startRow = range.getRow();
    const startCol = range.getColumn();
    const numRows = range.getNumRows();
    const numCols = range.getNumColumns();

    const editedValues = range.getValues();
    const headers = sheet.getRange(1, startCol, 1, numCols).getValues()[0];
    const firstColValues = sheet.getRange(startRow, 1, numRows).getValues();

    const sheetName = sheet.getName();
    const apiUrl = "{$url}";
    const payloadArray = [];

    for (let i = 0; i < numRows; i++) {
      for (let j = 0; j < numCols; j++) {
        const row = startRow + i;
        if (row === 1) continue;

        const editedValue = editedValues[i][j];
        if (editedValue === "") continue;

        const header = headers[j];
        const driverCode = firstColValues[i][0];
        const formattedDate = formatDateFromSheetAndHeader(sheetName, header);

        if (formattedDate && driverCode) {
          payloadArray.push({
            department_name: "{$departmentName}",
            driver_code: String(driverCode),
            result_ai: String(editedValue),
            date: formattedDate
          });
        }
      }
    }

    if (payloadArray.length > 0) {
      const options = {
        method: 'post',
        contentType: 'application/json',
        payload: JSON.stringify({ data: payloadArray }),
      };

      const response = UrlFetchApp.fetch(apiUrl, options);
      Logger.log('✅ API response: ' + response.getContentText());
    } else {
      Logger.log('⚠️ No valid data to send.');
    }

  } catch (error) {
    Logger.log('❌ API error: ' + error.message);
  }
}

function formatDateFromSheetAndHeader(sheetName, header) {
  const [year, month] = sheetName.split('_');
  const dayMatch = header.match(/^(\\d+)/);
  if (!dayMatch) return null;

  const day = dayMatch[1].padStart(2, '0');
  return `\${year}-\${month}-\${day}`;
}
JAVASCRIPT;

        return [
            'setup.js' => $setupJs,
            'main.js' => $mainJs
        ];
    }

    /**
     * Lưu script info vào database
     */
    public function saveScriptToDatabase(string $spreadsheetId, string $scriptId, string $departmentName, int $departmentId): void
    {
        GoogleScript::create([
            'spreadsheet_id' => $spreadsheetId,
            'script_id' => $scriptId,
            'department_name' => $departmentName,
            'department_id' => $departmentId,
            'status' => 'active',
            'script_info' => [
                'created_at' => now()->toISOString(),
                'department' => $departmentName,
                'webhook_url' => config('app.url') . '/api/sheets/webhook'
            ]
        ]);
    }

    /**
     * Kiểm tra script đã tồn tại cho spreadsheet
     */
    public function checkExistingScript(string $spreadsheetId): ?array
    {
        try {
            $script = GoogleScript::where('spreadsheet_id', $spreadsheetId)
                ->where('status', 'active')
                ->first();

            if (!$script) {
                return null;
            }

            // Kiểm tra script có tồn tại trên Google không
            $scriptInfo = $this->getScriptProject($script->script_id);

            if (!$scriptInfo['success']) {
                // Script không tồn tại trên Google, xóa khỏi database
                $script->update(['status' => 'deleted']);
                return null;
            }

            return [
                'script_id' => $script->script_id,
                'department_name' => $script->department_name,
                'status' => $script->status,
                'script_info' => $script->script_info,
                'google_info' => $scriptInfo
            ];

        } catch (\Exception $e) {
            Log::error("[checkExistingScript] Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo script project với container binding (bound to spreadsheet)
     */
    public function createScriptProjectWithContainer(string $title, string $spreadsheetId): array
    {
        try {
            $projectRequest = new CreateProjectRequest([
                'title' => $title,
                'parentId' => $spreadsheetId
            ]);

            $project = $this->scriptService->projects->create($projectRequest);

            Log::info("[createScriptProjectWithContainer] Created bound script project: {$project->getScriptId()} with title '{$title}' for spreadsheet: {$spreadsheetId}");

            return [
                'success' => true,
                'script_id' => $project->getScriptId(),
                'title' => $project->getTitle(),
                'createTime' => $project->getCreateTime(),
                'parent_id' => $project->getParentId() ?? $spreadsheetId,
                'bound_to_spreadsheet' => true
            ];

        } catch (\Exception $e) {
            Log::error("[createScriptProjectWithContainer] Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo Apps Script linked to spreadsheet
     */
    public function createLinkedAppsScriptForSpreadsheet(string $spreadsheetId, string $departmentName): string
    {
        try {
            // Tạo tên script với environment và department
            $scriptTitle = $this->getScriptNameWithEnvironment($departmentName);

            // Tạo script project linked to spreadsheet
            $projectResult = $this->createScriptProjectWithContainer(
                $scriptTitle,
                $spreadsheetId
            );

            if (!$projectResult['success']) {
                throw new \Exception("Failed to create bound script project: " . $projectResult['error']);
            }

            $scriptId = $projectResult['script_id'];

            // Tạo script content
            $scriptContent = $this->generateAppsScriptCode($departmentName);
            $contentResult = $this->createScriptContent($scriptId, $scriptContent);

            if (!$contentResult['success']) {
                throw new \Exception("Failed to create script content: " . $contentResult['error']);
            }

            // Lưu vào database
            $departmentId = $this->getDepartmentIdByName($departmentName);
            $this->saveScriptToDatabase($spreadsheetId, $scriptId, $departmentName, $departmentId);

            Log::info("[createLinkedAppsScriptForSpreadsheet] Created linked script {$scriptId} with title '{$scriptTitle}' for spreadsheet {$spreadsheetId}");

            return $scriptId;

        } catch (\Exception $e) {
            Log::error("[createLinkedAppsScriptForSpreadsheet] Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kiểm tra script có linked to spreadsheet không
     */
    public function isScriptLinkedToSpreadsheet(string $scriptId, string $spreadsheetId): array
    {
        try {
            $scriptInfo = $this->getScriptProject($scriptId);

            if (!$scriptInfo['success']) {
                return [
                    'success' => false,
                    'error' => 'Script not found',
                    'bound' => false
                ];
            }

            // Kiểm tra parent_id hoặc container
            $isLinked = false;
            $linkedInfo = null;

            if (isset($scriptInfo['parent_id']) && $scriptInfo['parent_id'] === $spreadsheetId) {
                $isLinked = true;
                $linkedInfo = [
                    'type' => 'parent_id',
                    'value' => $scriptInfo['parent_id']
                ];
            }

            if (isset($scriptInfo['container']) && $scriptInfo['container']['id'] === $spreadsheetId) {
                $isLinked = true;
                $linkedInfo = [
                    'type' => 'container',
                    'value' => $scriptInfo['container']
                ];
            }

            return [
                'success' => true,
                'linked' => $isLinked,
                'linked_info' => $linkedInfo,
                'script_info' => $scriptInfo
            ];

        } catch (\Exception $e) {
            Log::error("[isScriptLinkedToSpreadsheet] Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'linked' => false
            ];
        }
    }

    /**
     * Cập nhật script hiện có thay vì tạo mới
     */
    public function updateExistingScript(string $scriptId, string $departmentName): string
    {
        try {
            Log::info("[updateExistingScript] Bắt đầu cập nhật script: {$scriptId}");

            // 1. Kiểm tra script có tồn tại không
            $scriptInfo = $this->getScriptProject($scriptId);
            if (!$scriptInfo['success']) {
                throw new \Exception("Script {$scriptId} không tồn tại trên Google");
            }

            // 2. Cập nhật tên script với environment và department mới
            $newScriptTitle = $this->getScriptNameWithEnvironment($departmentName);
            $this->updateScriptTitle($scriptId, $newScriptTitle);
            Log::info("[updateExistingScript] Đã cập nhật tên script thành: {$newScriptTitle}");

            // 3. Cập nhật script content với code mới
            $scriptContent = $this->generateAppsScriptCode($departmentName);
            $contentResult = $this->createScriptContent($scriptId, $scriptContent);

            if (!$contentResult['success']) {
                throw new \Exception("Failed to update script content: " . $contentResult['error']);
            }

            // 3. Cập nhật thông tin trong database
            $existingScript = GoogleScript::where('script_id', $scriptId)->first();
            if ($existingScript) {
                $existingScript->update([
                    'department_name' => $departmentName,
                    'updated_at' => now(),
                    'script_info' => array_merge($existingScript->script_info ?? [], [
                        'last_updated' => now()->toISOString(),
                        'department' => $departmentName,
                        'update_type' => 'content_update'
                    ])
                ]);
                Log::info("[updateExistingScript] Đã cập nhật thông tin trong database");
            }

            // 4. Tạo deployment mới (optional)
            try {
                $deploymentResult = $this->createScriptDeployment($scriptId);
                if ($deploymentResult['success']) {
                    Log::info("[updateExistingScript] Đã tạo deployment mới: {$deploymentResult['deployment_id']}");
                }
            } catch (\Exception $e) {
                Log::warning("[updateExistingScript] Không thể tạo deployment mới: " . $e->getMessage());
                // Không throw exception vì deployment không bắt buộc
            }

            Log::info("[updateExistingScript] Hoàn thành cập nhật script: {$scriptId}");
            return $scriptId;

        } catch (\Exception $e) {
            Log::error("[updateExistingScript] Lỗi: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo hoặc cập nhật script cho spreadsheet
     */
    public function createOrUpdateScript(string $spreadsheetId, string $departmentName, bool $forceUpdate = false): string
    {
        try {
            Log::info("[createOrUpdateScript] Bắt đầu tạo/cập nhật script cho spreadsheet: {$spreadsheetId}");

            // Kiểm tra script hiện tại
            $existingScript = $this->checkExistingScript($spreadsheetId);

            if ($existingScript && $forceUpdate) {
                // Cập nhật script hiện có
                Log::info("[createOrUpdateScript] Cập nhật script hiện có: {$existingScript['script_id']}");
                return $this->updateExistingScript($existingScript['script_id'], $departmentName);
            } elseif ($existingScript && !$forceUpdate) {
                // Script đã tồn tại và không force update
                Log::info("[createOrUpdateScript] Script đã tồn tại, không cập nhật: {$existingScript['script_id']}");
                return $existingScript['script_id'];
            } else {
                // Tạo script mới
                Log::info("[createOrUpdateScript] Tạo script mới");
                return $this->createLinkedAppsScriptForSpreadsheet($spreadsheetId, $departmentName);
            }

        } catch (\Exception $e) {
            Log::error("[createOrUpdateScript] Lỗi: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy department ID từ tên department
     */
    private function getDepartmentIdByName(string $departmentName): ?int
    {
        $department = \App\Models\Department::where('name', $departmentName)->first();
        return $department ? $department->id : null;
    }
}
