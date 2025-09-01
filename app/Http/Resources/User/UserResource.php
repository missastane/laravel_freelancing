<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'mobile' => $this->mobile,
            'mobile_verified_at' => $this->mobile_verified_at,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender_value,
            'birth_date' => $this->birth_date,
            'avatar_photo' => $this->profile_photo_path,
            'activation' => $this->activation_value,
            'active_role' => $this->active_role,
            'about_me' => $this->about_me,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => RoleResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
