# Saotome in da house

ChatWorkのAPIを使って特定の人をいろんなグループチャットに追加するためのライブラリです。
（使用上起きた問題についての責任は負いかねます。ご自身の責任でお使いください！！！）

# 使い方の例
```php
$saotome = new Saotome('CHATWORK_API_TOKEN');

// コンタクトリストを取得
$contact_list = $saotome->getContacts();

// 同じ組織のコンタクトだけを取得したい場合
// $contact_list = $saotome->getContacts(true);


// 追加したい人
$account_id = 98765432112345678765432123456787654321;

// グループチャットを取得
$room_list = $saotome->getRooms();

// 追加したいグループチャット
$rooms = array(
    123 => 'admin',    // 管理者権限で追加
    234 => 'member',   // メンバー権限で追加
    345 => 'readonly', // 閲覧のみの権限で追加
);

$saotome->append($account_id, $rooms);
```
