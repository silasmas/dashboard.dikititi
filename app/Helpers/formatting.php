<?php
/**
 * @author Xanders
 * @see https://www.linkedin.com/in/xanders-samoth-b2770737/
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

if (! function_exists("getRandomNumber")) {
    function getRandomNumber($n)
    {
        $characters   = '0123456789';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
}
if (! function_exists("translatableOptions")) {
    function translatableOptions($items, string $labelKey, string $valueKey = 'id', ?string $locale = null, string $fallback = '[Sans nom]'): array
    {
        $locale = $locale ?? app()->getLocale();

        return $items->mapWithKeys(function ($item) use ($labelKey, $valueKey, $locale, $fallback) {
            $label = $item->{$labelKey};

            // Si c’est un tableau traduisible, on prend la langue, sinon valeur brute
            $value = is_array($label)
                ? ($label[$locale] ?? $fallback)
                : (is_string($label) ? $label : $fallback);

            return [$item->{$valueKey} => $value];
        })->toArray();
    }
}
if (! function_exists("countUsersByRole")) {
    function countUsersByRole(string $roleName): int
    {
        return DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.role_name', $roleName)
            ->count();
    }
}

if (! function_exists("formatIntegerNumber")) {
    function formatIntegerNumber($number)
    {
        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');

            if ($sessionLocale !== 'fr') {
                return number_format($number, 0, '.', ',');

            } else {
                return number_format($number, 0, ',', ' ');
            }
        } else {
            $appLocale = app()->getLocale();

            if ($appLocale !== 'fr') {
                return number_format($number, 0, '.', ',');

            } else {
                return number_format($number, 0, ',', ' ');
            }
        }
    }
}

if (! function_exists("resolveMediaId")) {
     function resolveMediaId(?\Filament\Forms\Form $form = null): int
     {
         // Si on est dans une édition (record déjà existant dans le form)
         if ($form && $form->getRecord()) {
             return $form->getRecord()->id;
         }

         // Sinon, on génère le prochain ID (création)
         $last = \App\Models\Media::latest('id')->first();
         return $last ? $last->id + 1 : 1;
    }

}
if (! function_exists("formatDecimalNumber")) {
    function formatDecimalNumber($number)
    {
        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');

            if ($sessionLocale !== 'fr') {
                return number_format($number, 2, '.', ',');

            } else {
                return number_format($number, 2, ',', ' ');
            }
        } else {
            $appLocale = app()->getLocale();

            if ($appLocale !== 'fr') {
                return number_format($number, 2, '.', ',');

            } else {
                return number_format($number, 2, ',', ' ');
            }
        }
    }
}

if (! function_exists("timeAgo")) {
    function timeAgo($datetime, $full = false)
    {
        $now  = new DateTime;
        $ago  = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');

            switch ($sessionLocale) {
                case 'en':
                    $string = [
                        'y' => 'year',
                        'm' => 'month',
                        'w' => 'week',
                        'd' => 'day',
                        'h' => 'hour',
                        'i' => 'minute',
                        's' => 'second',
                    ];

                    foreach ($string as $k => &$v) {
                        if ($diff->$k) {
                            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');

                        } else {
                            unset($string[$k]);
                        }
                    }

                    if (! $full) {
                        $string = array_slice($string, 0, 1);
                    }

                    return $string ? implode(', ', $string) . ' ago' : 'just now';
                    break;

                default:
                    $string = [
                        'y' => 'an',
                        'm' => 'mois',
                        'w' => 'semaine',
                        'd' => 'jour',
                        'h' => 'heure',
                        'i' => 'minute',
                        's' => 'seconde',
                    ];

                    foreach ($string as $k => &$v) {
                        if ($diff->$k) {
                            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 && $v !== 'mois' ? 's' : '');

                        } else {
                            unset($string[$k]);
                        }
                    }

                    if (! $full) {
                        $string = array_slice($string, 0, 1);
                    }

                    return $string ? 'Il y a ' . implode(', ', $string) : 'en ce moment';
                    break;
            }

        } else {
            $appLocale = app()->getLocale();

            switch ($appLocale) {
                case 'en':
                    $string = [
                        'y' => 'year',
                        'm' => 'month',
                        'w' => 'week',
                        'd' => 'day',
                        'h' => 'hour',
                        'i' => 'minute',
                        's' => 'second',
                    ];

                    foreach ($string as $k => &$v) {
                        if ($diff->$k) {
                            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');

                        } else {
                            unset($string[$k]);
                        }
                    }

                    if (! $full) {
                        $string = array_slice($string, 0, 1);
                    }

                    return $string ? implode(', ', $string) . ' ago' : 'just now';
                    break;

                default:
                    $string = [
                        'y' => 'an',
                        'm' => 'mois',
                        'w' => 'semaine',
                        'd' => 'jour',
                        'h' => 'heure',
                        'i' => 'minute',
                        's' => 'seconde',
                    ];

                    foreach ($string as $k => &$v) {
                        if ($diff->$k) {
                            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 && $v !== 'mois' ? 's' : '');

                        } else {
                            unset($string[$k]);
                        }
                    }

                    if (! $full) {
                        $string = array_slice($string, 0, 1);
                    }

                    return $string ? 'Il y a ' . implode(', ', $string) : 'en ce moment';
                    break;
            }
        }
    }
}
