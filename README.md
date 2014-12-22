# Saotome in da house

# 使い方の例

```php
$saotome = new Saotome('CHATWORK_API_TOKEN');

// 同じ組織の人だけを取得したい場合
// $saotome->checkClientOrganization();

// コンタクトリストを取得
$contact_list = $saotome->getContacts();

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
