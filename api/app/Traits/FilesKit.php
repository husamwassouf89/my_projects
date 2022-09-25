<?php


namespace App\Traits;


use Carbon\Carbon;
use Symfony\Component\HttpFoundation\File\File;

trait FilesKit
{

    public function saveFile(File $file, $path = 'attachments'): ?string
    {
        $path = 'storage/' . $path;
        $name = $this->generateMediaName($file->getClientOriginalName());
//        $name = str_replace(' ', '_', $name);
//        $name = str_replace("\'", '_', $name);
        if (!$file->move($path, $name)) return null;
        return $path . '/' . $name;
    }

    public function generateMediaName($originalName)
    {
        $explode   = explode(".", $originalName);
        $extension = $explode[count($explode) - 1];
        $start     = Carbon::create("1970", "01", "01");
        $end       = Carbon::now();
        $seconds   = $end->diffInSeconds($start);
        return $originalName . '-' . mt_rand(1000, 9999) . $seconds . '_' . Carbon::now()->format('Y_m_d_h_i_s') . "." . $extension;
    }


}
