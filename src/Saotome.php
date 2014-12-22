<?php

use GuzzleHttp\Client;

class Saotome
{
    /** @var GuzzleHttp\Client */
    private $client;

    /** @var int 依頼者の組織ID */
    private $organization_id = null;

    /**
     * コンストラクタ
     *
     * @param string APIトークン
     */
    public function __construct($token = "")
    {
        if (empty($token)) {
            throw new Exception("APIトークンを教えてって言ったじゃない…");
        }

        $this->client = new Client(array(
            'base_url' => 'https://api.chatwork.com',
            'defaults' => array(
                'headers' => array(
                    'X-ChatWorkToken' => $token,
                ),
            ),
        ));

        $this->token = $token;
    }

    /**
     * 依頼者の情報を取得
     *
     * @return array
     */
    public function getClientInfo()
    {
        return $this->client->get('/v1/me')->json();
    }

    /**
     * 自分のコンタクト一覧を取得する
     *
     * @param  bool  同じ組織の人だけを取得する
     * @return array
     */
    public function getContacts($same_organization = false)
    {
        $contacts = $this->client->get('/v1/contacts')->json();

        if ($same_organization === false) {
            return $contacts;
        }

        $client_info = $this->getClientInfo();

        $within = array();

        foreach ($contacts as $contact) {
            if ($client_info['organization_id'] !== $contact['organization_id']) {
                continue;
            }

            $within[] = $contact;
        }

        return $within;
    }

    /**
     * 自分が追加出来るグループチャット一覧を取得する
     *
     * @return array
     */
    public function getRooms()
    {
        $joinable = array();
        $rooms    = $this->client->get('/v1/rooms')->json();

        foreach ($rooms as $room) {
            if ($room['type'] !== 'group') {
                continue;
            }

            if ($room['role'] !== 'admin') {
                continue;
            }

            $joinable[] = $room;
        }

        return $joinable;
    }

    /**
     * グループチャットに追加する
     *
     * @param  int   追加する人のアカウントID
     * @param  array 追加するグループチャットとその権限
     * @return array
     */
    public function append($account_id, $rooms)
    {
        $success = array();
        $failure = array();

        foreach ($rooms as $room_id => $role) {
            $members = $this->getMembers($room_id);
            $joined  = $this->alreadyJoined($account_id, $members);

            if ($joined !== false) {
                $failure[$room_id] = $joined;
                continue;
            }

            $members[$role][] = $account_id;

            $body = array_filter(array(
                'members_admin_ids'    => implode(',', $members['admin']),
                'members_member_ids'   => implode(',', $members['member']),
                'members_readonly_ids' => implode(',', $members['readonly']),
            ));

            $endpoint = sprintf('/v1/rooms/%d/members', $room_id);
            $this->client->put($endpoint, array(
                'body' => $body,
            ));

            $success[$room_id] = $role;
        }

        return array(
            'success' => $success,
            'failire' => $failure,
        );
    }

    /**
     * メンバーを取得
     *
     * @param  int
     * @return array
     */
    private function getMembers($room_id)
    {
        $members = array(
            'admin'    => array(),
            'member'   => array(),
            'readonly' => array(),
        );

        $endpoint = sprintf('/v1/rooms/%d/members', $room_id);
        $results  = $this->client->get($endpoint)->json();

        foreach ($results as $member) {
            $role = $member['role'];

            $members[$role][] = $member['account_id'];
        }

        return $members;
    }

    /**
     * 既に参加済みかどうかを判別する
     *
     * 参加済みの場合はどの権限で参加しているかを返す
     *
     * @param  int
     * @param  array
     * @return string|bool
     */
    private function alreadyJoined($account_id, $members)
    {
        if (in_array($account_id, $members['admin'])) {
            return 'admin';
        } else if (in_array($account_id, $members['member'])) {
            return 'member';
        } else if(in_array($account_id, $members['readonly'])) {
            return 'readonly';
        }

        return false;
    }
}
