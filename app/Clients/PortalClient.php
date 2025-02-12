<?php

namespace Portal\Clients;

use App\Models\User;

class PortalClient extends Client
{
    protected $client;

    public function __construct()
    {
        $this->setUrl(env('PORTAL_URL'));
    }

    public function getUser($uuid) {
        return new User($this->get('/api/user/'.$uuid)->response->json());
    }

    public function getSharesByUserUuid($uuid) {
        return $this->get('/api/shares/user/'.$uuid)->response->json();
    }

    public function addShare($projectUuid, $createdBy, $sharedWith) {
        
        $share = [
            'user_id'     => $sharedWith->id,
            'group_id'    => $sharedWith->group_id,
            'project_uuid'=> $projectUuid,
            'created_by'  => $createdBy->username
        ];

        $this->post('/api/shares',$share)->logErrors();
    }
}