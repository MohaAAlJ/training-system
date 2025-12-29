<?php

use App\Models\Administrative;

$admins = Administrative::with('governorate')->get();

foreach ($admins as $admin) {
    echo "ID: " . $admin->id . "\n";
    echo "Title: " . $admin->title . "\n";
    echo "Gov ID: " . $admin->governorate_id . "\n";
    echo "Gov Name (Raw): " . ($admin->governorate ? GetGovName($admin->governorate) : 'NULL') . "\n";
    echo "Accessor: " . $admin->name_with_governorate . "\n";
    echo "-------------------\n";
}

function GetGovName($gov)
{
    $name = $gov->name;
    if (is_array($name)) {
        return json_encode($name, JSON_UNESCAPED_UNICODE);
    }
    return $name;
}
