<!DOCTYPE html>
<html>
<body>
<h3>パスワードをお忘れですか？</h3>
<p>以下のリンクをクリックすると、Izumi appのパスワードをリセットできます。</p>
<p>URL: <a href="{{URL::to('/reset-password?value='.$detail['value'])}}">パスワードをリセット</a></p>
<p>従業員番号：{{ $detail['emp_code'] }}</p>
<p>パスワードのリセットをご希望でない場合、このEメールは削除していただいてかまいません。</p>
<p>よろしくお願いいたします</p>
<p>イズミ物流株式会社</p>
</body>
</html>
