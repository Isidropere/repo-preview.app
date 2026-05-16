<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$u12 = User::find(12);
if ($u12) {
    $u12->password = Hash::make('password123');
    $u12->save();
    echo "User 12 password updated\n";
}

$u8 = User::find(8);
if ($u8) {
    $u8->password = Hash::make('password123');
    $u8->save();
    echo "User 8 password updated\n";
}
