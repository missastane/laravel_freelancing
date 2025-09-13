<?php

namespace App\Policies;

use App\Models\Market\File;
use App\Models\User\User;

class FilePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function deleteFile(User $user,File $file)
    {
        return $file->uploaded_by == $user->id;
    }
    
}
