<head>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        table.table-list {
            margin-top: 10px;
        }

        table.table-list {
            width: 100%;
            border-collapse: collapse;
        }

        table.table-detail {
            margin-top: 10px;
        }

        table.table-list tr.tr-info {
            background-color: #0F0049;
            color: #FFF;
            font-weight: 600;
        }

        table.table-list tr.tr-info td {
            width: 80%;
        }

        table.table-list tr td {
            border: 2px solid #dee2e6;
            padding: .5rem;
        }

        table.table-list {
            padding: .5rem;
            text-align: center;
            background-color: #F2F2F2;
            border-radius: .5rem;
        }

        table.table-list span {
            font-weight: 600;
        }

        table.table-list .content {
            text-align: left;
            background-color: #FFFFFF;
            color: #000000;
        }
    </style>
</head>
<body>
    <!-- Main page -->
    <div class="container">

        <!-- Table List -->
        <table class="table table-list">
            <tbody>
                <tr class="tr-info">
                    <td>
                        <span>イズミクラウド上でのデータ連携に失敗がありました。</span>
                    </td>
                </tr>

                <tr>
                    <td class="content">
                        <span>連携失敗箇所：{{$from}} から {{$to}} への連携</span>
                    </td>
                </tr>

                <tr>
                    <td class="content">
                        <span>データ名：{{$dataName}}</span>
                    </td>
                </tr>

                <tr>
                    <td class="content">
                        <span>連携失敗日時：{{$date}}</span>
                    </td>
                </tr>

                <tr>
                    <td class="content">
                        <span>連携失敗エラーメッセージ：{{$content}}</span>
                    </td>
                </tr>

                <tr class="tr-info">
                    <td>連携の失敗原因に心当たりがない場合は開発担当者までご連絡下さい。</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
