<?php

declare(strict_types=1);

return [
    'brand' => 'PawCircle',
    'guest' => 'Svečias',
    'logout' => 'Atsijungti',
    'connection' => [
        'offline' => 'Nėra interneto ryšio. Pakeitimų negalima pateikti, kol ryšys neatsinaujins.',
    ],
    'form' => [
        'unsaved' => 'Yra neišsaugotų pakeitimų.',
    ],
    'accessibility' => [
        'skip_to_content' => 'Pereiti prie turinio',
    ],
    'fields' => [
        'name' => 'Vardas',
        'email' => 'El. pašto adresas',
        'password' => 'Slaptažodis',
        'password_confirmation' => 'Pakartokite slaptažodį',
        'locale' => 'Kalba',
        'timezone' => 'Laiko juosta',
    ],
    'locales' => [
        'en' => 'Anglų',
        'lt' => 'Lietuvių',
        'ru' => 'Rusų',
    ],
    'login' => [
        'title' => 'Prisijungti',
        'description' => 'Prisijunkite, kad pasiektumėte privačius augintinio priežiūros, sveikatos ir įrenginių duomenis.',
        'failed' => 'Šie duomenys neatitinka aktyvios paskyros.',
        'account_unavailable' => 'Ši paskyra negali pasiekti apsaugotų funkcijų.',
        'throttled' => 'Per daug bandymų. Bandykite po :seconds sek.',
        'forgot_password' => 'Pamiršote slaptažodį?',
        'remember' => 'Likti prisijungus',
        'submit' => 'Prisijungti',
        'submitting' => 'Jungiama…',
        'no_account' => 'Dar neturite paskyros?',
        'register' => 'Sukurti paskyrą',
    ],
    'register' => [
        'title' => 'Sukurkite paskyrą',
        'description' => 'Jūsų privatūs duomenys prieinami tik jūsų įgaliotiems žmonėms.',
        'timezone_help' => 'Naudokite IANA laiko juostą, pavyzdžiui, Europe/Vilnius.',
        'password_help' => 'Bent 12 simbolių, didžioji ir mažoji raidė bei skaičius.',
        'submit' => 'Sukurti paskyrą',
        'submitting' => 'Kuriama paskyra…',
        'has_account' => 'Jau turite paskyrą?',
        'login' => 'Prisijungti',
    ],
    'password' => [
        'forgot_title' => 'Atkurti slaptažodį',
        'forgot_description' => 'Įveskite el. paštą. Jei paskyra yra, išsiųsime saugią atkūrimo nuorodą.',
        'link_sent' => 'Jei toks adresas susietas su paskyra, atkūrimo nuoroda išsiųsta.',
        'send_link' => 'Siųsti atkūrimo nuorodą',
        'sending' => 'Siunčiama…',
        'back_to_login' => 'Grįžti į prisijungimą',
        'reset_title' => 'Pasirinkite naują slaptažodį',
        'reset_description' => 'Įveskite su nuoroda susietą el. paštą ir naują saugų slaptažodį.',
        'reset_submit' => 'Atkurti slaptažodį',
        'resetting' => 'Atkuriama…',
        'reset_success' => 'Slaptažodis atkurtas. Dabar galite prisijungti.',
    ],
    'confirm_password' => [
        'title' => 'Patvirtinkite slaptažodį',
        'description' => 'Prieš tęsdami apsaugotą veiksmą dar kartą įveskite slaptažodį.',
        'failed' => 'Įvestas slaptažodis neatitinka jūsų paskyros.',
        'throttled' => 'Per daug bandymų. Bandykite dar kartą po :seconds sek.',
        'submit' => 'Patvirtinti slaptažodį',
        'submitting' => 'Tikrinama…',
    ],
    'verification' => [
        'title' => 'Patvirtinkite el. paštą',
        'description' => 'Prieš naudodami apsaugotas funkcijas atidarykite el. paštu gautą patvirtinimo nuorodą.',
        'resend' => 'Siųsti patvirtinimo laišką dar kartą',
        'sending' => 'Siunčiama…',
        'sent' => 'Naujas patvirtinimo laiškas išsiųstas.',
        'success' => 'El. pašto adresas patvirtintas.',
    ],
];
