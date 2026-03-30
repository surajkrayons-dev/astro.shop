<?php
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

if (!function_exists('objFromPost')) {
    function objFromPost($fieldArr = [])
    {
        $request = request();
        $output = new \stdClass;
        if (count($fieldArr)) {
            foreach ($fieldArr as $value) {
                $val = $request->input($value);
                $output->$value = in_array(gettype($val), ['integer', 'double', 'string']) ? trim($val) : $val;
            }
        }
        return $output;
    }
}

if (!function_exists('generateFilename')) {
    function generateFilename()
    {
        return str_replace([' ', ':', '-'], '', \Carbon\Carbon::now()->toDateTimeString()) . generateRandomString(10, 'lower_case,upper_case,numbers');
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 6, $characters = 'upper_case,lower_case,numbers')
    {
        // $length - the length of the generated password
        // $count - number of passwords to be generated
        // $characters - types of characters to be used in the password

        // define variables used within the function
        $symbols = array();
        $passwords = array();
        $used_symbols = '';
        $pass = '';

        // an array of different character types
        $symbols['lower_case'] = 'abcdefghijklmnopqrstuvwxyz';
        $symbols['upper_case'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols['numbers'] = '1234567890';
        $symbols['special_symbols'] = '!?~@#-_+<>[]{}';

        $characters = explode(',', $characters); // get characters types to be used for the password
        foreach ($characters as $key => $value) {
            $used_symbols .= $symbols[$value]; // build a string with all characters
        }
        $symbols_length = strlen($used_symbols) - 1; //strlen starts from 0 so to get number of characters deduct 1

        for ($p = 0; $p < 1; ++$p) {
            $pass = '';
            for ($i = 0; $i < $length; ++$i) {
                $n = rand(0, $symbols_length); // get a random character from the string with all characters
                $pass .= $used_symbols[$n]; // add the character to the password string
            }
            $passwords = $pass;
        }

        return $passwords; // return the generated password
    }
}

if (!function_exists('filterMobileNo')) {
    function filterMobileNo($mobile = null, $dial_code = '91')
    {
        if (!$mobile || !$dial_code) {
            return '';
        }

        $mobile = str_replace('+', '', $mobile);
        if (substr($mobile, 0, strlen($dial_code)) === $dial_code) {
            $mobile = substr($mobile, strlen($dial_code));
        } elseif (substr($mobile, 0, 1) == "0") {
            $mobile = substr($mobile, 1);
        }

        return $mobile;
    }
}

if (!function_exists('getBtwDays')) {
    function getBtwDays($from_date = null, $to_date = null)
    {
        if (!$from_date || !$to_date) {
            return 0;
        }

        $from_date = new \DateTime($from_date);
        $to_date = new \DateTime($to_date);
        $interval = $from_date->diff($to_date);
        return $interval->format('%a');
    }
}

if (!function_exists('getRandomNumber')) {
    function getRandomNumber($digits = 6)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
}

if (!function_exists('getDaysBetweenDates')) {
    function getDaysBetweenDates($startDate, $endDate, $format = 'Y-m-d')
    {
        $response = [];

        $total_months = \Carbon\Carbon::parse($endDate)->diffInDays(\Carbon\Carbon::parse($startDate)) / 30;
        $format = $total_months >= 2 ? 'M Y' : $format;

        $interval = \DateInterval::createFromDateString($total_months >= 2 ? '1 month' : '1 day');
        $period = new \Carbon\CarbonPeriod($startDate, $interval, $endDate);
        foreach ($period as $date) {
            $response[] = $date->format($format);
        }

        return [
            'dates' => $response,
            'date_format' => $format,
            'sql_date_format' => $format == 'Y-m-d' ? '%Y-%m-%d' : '%b %Y',
        ];
    }
}

if (!function_exists('strpos_arr')) {
    function strpos_arr($haystack, $needle)
    {
        $response = [];
        $needle = !is_array($needle) ? [$needle] : $needle;

        foreach ($needle as $key => $what) {
            if (($pos = strpos($what, $haystack)) !== false) {
                $response[] = $key;
            }
        }
        return $response;
    }
}

if (!function_exists('compareNumbers')) {
    function compareNumbers($number1, $number2)
    {
        if ($number2) {
            return (abs(($number1 - $number2) / $number2) < 0.00001);
        }
        return ((float) $number1 == (float) $number2);
    }
}

if (!function_exists('buildHierarchyTree')) {
    function buildHierarchyTree($elements, $parentId = null)
    {
        $branch = collect();

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = buildHierarchyTree($elements, $element->id);
                if ($children->count()) {
                    $element->children = $children;
                }
                $branch->add($element);
            }
        }

        return $branch;
    }
}

if (!function_exists('isCurrentRoute')) {
    function isCurrentRoute($routeName)
    {
        $routeName = substr($routeName, 0, strrpos($routeName, '.'));

        $currentRoute = \Route::currentRouteName();
        $currentRoute = substr($currentRoute, 0, strrpos($currentRoute, '.'));

        return $routeName == $currentRoute;
    }
}

if (!function_exists('generateOrderCode')) {
    function generateOrderCode()
    {
        $order_code = 'VRE-' . getRandomNumber(10);

        if (\App\Models\Order::whereOrderCode($order_code)->exists()) {
            return generateOrderCode();
        }

        return $order_code;
    }
}

if (!function_exists('getAppSettings')) {
    function getAppSettings($attribute = null)
    {
        $key = \App\Models\Setting::CACHE_KEY;
        // \Cache::pull($key);
        // \Cache::flush();

        $settings = \Cache::rememberForever($key, fn () => \App\Models\Setting::pluck('value', 'attribute'));

        return $attribute ? $settings[$attribute] : $settings;
    }
}

if (!function_exists('getOrderStatusesForUpdate')) {
    function getOrderStatusesForUpdate($status)
    {
        $output = [];
        foreach (config('system.order_statuses') as $key => $value) {
            $output[$key] = [
                'value' => $key,
                'text' => $value,
                'is_selected' => $key == $status,
                'is_disabled' => true,
            ];
        }

        $output[$status]['is_disabled'] = false;

        if ($status == 'pending') {
            $output['processing']['is_disabled'] = false;
            $output['cancelled']['is_disabled'] = false;
        } elseif ($status == 'processing') {
            $output['shipped']['is_disabled'] = false;
            $output['cancelled']['is_disabled'] = false;
        } elseif ($status == 'shipped') {
            $output['completed']['is_disabled'] = false;
            $output['cancelled']['is_disabled'] = false;
        } elseif ($status == 'completed') {
            $output['returned']['is_disabled'] = false;
        }

        return $output;
    }
}

if (!function_exists('getGstSettings')) {
    function getGstSettings()
    {
        $gst_settings = getAppSettings('company_gst_settings');
        return array_filter(explode(',', $gst_settings), fn ($val) => $val !== '' && !is_null($val));
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($num, $decimals = 2)
    {
        return str_replace('.00', '', number_format($num, $decimals));
    }
}

// if (!function_exists('uploadFile')) {

//     function uploadFile($filename, $cropWidth = null, $cropHeight = null, $folder = 'default', $oldFileName = null)
//     {
//         try {
//             $file = request()->file($filename);

//             if (!$file) {
//                 throw new \Exception("File not found in the request.");
//             }

//             // Remove the old file if it exists
//             if ($oldFileName) {
//                 $oldFilePath = "public/{$folder}/" . $oldFileName;
//                 Storage::delete($oldFilePath);
//             }

//             // Generate a unique filename
//             // $randomString = random_int(0, PHP_INT_MAX) . strtotime(now());
//             $randomString = substr(hash('sha256', uniqid(mt_rand(), true)), 0, 10);
//             $newFilename = $randomString . '.webp';

//             // Define the storage path
//             $path = "public/{$folder}/" . $newFilename;

//             // Load the image and optionally crop it
//             $image = Image::make($file);
//             if ($cropWidth && $cropHeight) {
//                 $image->fit($cropWidth, $cropHeight);
//             }

//             // Convert and save the image in WebP format
//             $image->encode('webp', 80);

//             // Store the processed image
//             Storage::put($path, (string)$image);

//             return $newFilename; // Return the stored file name
//         } catch (\Throwable $th) {
//             \Log::error($th);
//             return null;
//         }
//     }
// }
if (!function_exists('uploadFile')) {

    function uploadFile($filename, $cropWidth = null, $cropHeight = null, $folder = 'default', $oldFileName = null)
    {
        try {
            $file = request()->file($filename);

            if (!$file) {
                throw new \Exception("File not found in the request.");
            }

            // Paths
            $publicPath  = public_path("storage/{$folder}");
            $storagePath = storage_path("app/public/{$folder}");

            // Ensure folders exist
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Delete old image from BOTH places
            if ($oldFileName) {
                $oldPublic  = $publicPath . '/' . $oldFileName;
                $oldStorage = $storagePath . '/' . $oldFileName;

                if (file_exists($oldPublic)) {
                    unlink($oldPublic);
                }
                if (file_exists($oldStorage)) {
                    unlink($oldStorage);
                }
            }

            // Unique filename
            $randomString = substr(hash('sha256', uniqid(mt_rand(), true)), 0, 10);
            $newFilename = $randomString . '.webp';

            // Image process
            $image = Image::make($file);
            if ($cropWidth && $cropHeight) {
                $image->fit($cropWidth, $cropHeight);
            }
            $image->encode('webp', 80);

            // Save to BOTH locations
            $image->save($publicPath . '/' . $newFilename);
            $image->save($storagePath . '/' . $newFilename);

            return $newFilename;

        } catch (\Throwable $th) {
            \Log::error($th);
            return null;
        }
    }
}

if (!function_exists('uploadMultipleFiles')) {

    function uploadMultipleFiles(
        string $fieldName,
        ?int $cropWidth = null,
        ?int $cropHeight = null,
        string $folder = 'default'
    ): array {

        $uploadedFiles = [];

        try {
            $files = request()->file($fieldName);

            if (!$files || !is_array($files)) {
                return [];
            }

            // Paths
            $publicPath  = public_path("storage/{$folder}");
            $storagePath = storage_path("app/public/{$folder}");

            // Ensure folders exist
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            foreach ($files as $file) {

                // Unique filename
                $randomString = substr(hash('sha256', uniqid(mt_rand(), true)), 0, 10);
                $newFilename  = $randomString . '.webp';

                // Image process
                $image = Image::make($file);

                if ($cropWidth && $cropHeight) {
                    $image->fit($cropWidth, $cropHeight);
                }

                $image->encode('webp', 80);

                // Save to BOTH locations
                $image->save($publicPath . '/' . $newFilename);
                $image->save($storagePath . '/' . $newFilename);

                $uploadedFiles[] = $newFilename;
            }

            return $uploadedFiles;

        } catch (\Throwable $th) {
            \Log::error($th);
            return [];
        }
    }
}

if (!function_exists('uploadMedia')) {

    function uploadMedia(
        string $fieldName,
        ?int $cropWidth = null,
        ?int $cropHeight = null,
        string $folder = 'banners',
        ?string $oldFileName = null
    ) {

        try {

            $file = request()->file($fieldName);

            if (!$file) {
                return null;
            }

            $publicPath  = public_path("storage/{$folder}");
            $storagePath = storage_path("app/public/{$folder}");

            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            // Delete old file
            if ($oldFileName) {
                @unlink($publicPath . '/' . $oldFileName);
                @unlink($storagePath . '/' . $oldFileName);
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $randomString = substr(hash('sha256', uniqid(mt_rand(), true)), 0, 10);

            if (in_array($extension, ['mp4', 'mov', 'avi', 'webm'])) {

                $newFilename = $randomString . '.' . $extension;

                $file->move($publicPath, $newFilename);
                copy($publicPath . '/' . $newFilename, $storagePath . '/' . $newFilename);

                return [
                    'type' => 'video',
                    'path' => "{$folder}/{$newFilename}"
                ];
            }

            $newFilename = $randomString . '.webp';

            $image = Image::make($file);

            if ($cropWidth && $cropHeight) {
                $image->fit($cropWidth, $cropHeight);
            }

            $image->encode('webp', 80);

            $image->save($publicPath . '/' . $newFilename);
            $image->save($storagePath . '/' . $newFilename);

            return [
                'type' => 'image',
                'path' => "{$folder}/{$newFilename}"
            ];

        } catch (\Throwable $th) {
            \Log::error($th);
            return null;
        }
    }
}

if (!function_exists('calculateDistance')) {
    function calculateDistance($userLat, $userLong, $storeLat, $storeLong)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($storeLat - $userLat);
        $dLon = deg2rad($storeLong - $userLong);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($userLat)) * cos(deg2rad($storeLat)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c; // meters
    }
}

if (!function_exists('getAllSubordinateIds')) {
    /**
     * 🔹 Get all nested subordinate staff IDs recursively for multiple clients.
     *
     * @param int $userId
     * @param array|string $clientIds - can be array or comma-separated string
     * @return array
     */
    function getAllSubordinateIds($userId, $clientIds = [])
    {
        if (is_string($clientIds)) {
            $clientIds = array_filter(explode(',', $clientIds));
        }

        $allIds = [];

        // 🔸 Get all direct sub-staff under this user
        $query = \App\Models\User::where('parent_id', $userId)
            ->where('type', 'staff');

        // Filter by one or more client IDs
        if (!empty($clientIds)) {
            $query->where(function ($q) use ($clientIds) {
                foreach ($clientIds as $cid) {
                    $q->orWhere('client_id', 'like', "%{$cid}%");
                }
            });
        }

        $directSubs = $query->pluck('id');

        foreach ($directSubs as $subId) {
            $allIds[] = $subId;
            // Recursive call to get nested staff
            $allIds = array_merge($allIds, getAllSubordinateIds($subId, $clientIds));
        }

        return $allIds;
    }
}