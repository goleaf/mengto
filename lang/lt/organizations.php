<?php

declare(strict_types=1);

return [
    'types' => [
        'community' => 'Bendruomeninė organizacija',
        'shelter' => 'Prieglauda',
        'rescue' => 'Gyvūnų gelbėjimo organizacija',
        'professional' => 'Profesinė organizacija',
        'venue' => 'Renginio vieta',
        'marketplace' => 'Prekyvietės organizacija',
        'platform' => 'Platformos organizacija',
    ],
    'statuses' => ['draft' => 'Juodraštis', 'active' => 'Aktyvi', 'suspended' => 'Sustabdyta', 'archived' => 'Archyvuota'],
    'verification_statuses' => [
        'not_assessed' => 'Neįvertinta', 'pending' => 'Laukiama patvirtinimo', 'verified' => 'Patvirtinta',
        'expired' => 'Patvirtinimas nebegalioja', 'rejected' => 'Patvirtinimas atmestas', 'disputed' => 'Patvirtinimas ginčijamas',
    ],
    'roles' => ['owner' => 'Savininkas', 'administrator' => 'Administratorius', 'event_manager' => 'Renginių vadovas', 'finance_manager' => 'Finansų vadovas', 'safety_lead' => 'Saugos vadovas', 'marketplace_manager' => 'Prekyvietės vadovas', 'shelter_coordinator' => 'Prieglaudos koordinatorius', 'member' => 'Narys', 'auditor' => 'Tik skaitantis auditorius'],
    'membership_statuses' => ['invited' => 'Pakviestas', 'active' => 'Aktyvus', 'removed' => 'Pašalintas', 'expired' => 'Nebegalioja'],
    'invitation_statuses' => ['pending' => 'Laukiama', 'accepted' => 'Priimtas', 'declined' => 'Atmestas', 'revoked' => 'Atšauktas', 'expired' => 'Nebegalioja'],
    'restriction_capabilities' => [
        'create_events' => 'Kurti renginius', 'publish_events' => 'Skelbti renginius', 'accept_registrations' => 'Priimti registracijas',
        'accept_payments' => 'Priimti mokėjimus', 'access_participant_data' => 'Pasiekti dalyvių duomenis', 'run_check_in' => 'Vykdyti registravimą atvykus',
        'enter_results' => 'Įvesti rezultatus', 'create_invitations' => 'Kurti kvietimus',
    ],
    'pages' => [
        'index' => ['eyebrow' => 'Organizacijos įgaliojimai', 'title' => 'Organizacijos', 'description' => 'Vienoje darbo erdvėje tvarkykite vaidmenis, narystes, kvietimus ir atsakomybę už renginius.', 'create_eyebrow' => 'Nauja organizacija', 'create_title' => 'Sukurti organizacijos profilį', 'yours_title' => 'Jūsų organizacijos', 'yours_description' => 'Rodomos tik organizacijos, kuriose turite galiojančią narystę.'],
        'show' => ['eyebrow' => 'Organizacijos darbo erdvė', 'title' => 'Organizacijos darbo erdvė', 'description' => 'Narystė ir organizacijos įgaliojimai.', 'identity_eyebrow' => 'Tapatybė', 'identity_title' => 'Organizacijos įgaliojimai', 'members_eyebrow' => 'Narystė', 'invite_title' => 'Pakviesti narį', 'members_title' => 'Nariai', 'safety_eyebrow' => 'Sauga ir prieiga', 'restrictions_title' => 'Veiklos apribojimai'],
        'invitation' => ['eyebrow' => 'Su paskyra susietas kvietimas', 'title' => 'Kvietimas į organizaciją', 'description' => 'Prieš atsakydami peržiūrėkite organizaciją ir vaidmenį.', 'details_eyebrow' => 'Kvietimo informacija'],
    ],
    'fields' => ['name' => 'Organizacijos pavadinimas', 'summary' => 'Viešas aprašymas', 'type' => 'Organizacijos tipas', 'public_region' => 'Viešas regionas', 'verification' => 'Patvirtinimas', 'members' => 'Nariai', 'owner' => 'Savininkas', 'invite_email' => 'Kviečiamojo el. paštas', 'role' => 'Vaidmuo', 'expires_at' => 'Galioja iki', 'removal_reason' => 'Pašalinimo priežasties kodas', 'capability' => 'Ribojamas veiksmas', 'reason_code' => 'Priežasties kodas', 'suspension_reason' => 'Sustabdymo priežasties kodas'],
    'actions' => ['create' => 'Sukurti organizaciją', 'creating' => 'Kuriama...', 'open_workspace' => 'Atverti darbo erdvę', 'back_to_directory' => 'Grįžti į organizacijas', 'invite' => 'Sukurti kvietimą', 'remove_member' => 'Pašalinti narį', 'remove_confirmation' => 'Pašalinti narį ir atšaukti būsimą prieigą?', 'apply_restriction' => 'Taikyti apribojimą', 'suspend' => 'Sustabdyti organizaciją', 'suspend_confirmation' => 'Sustabdyti organizaciją ir jos renginių veiksmus?', 'accept_invitation' => 'Priimti kvietimą', 'decline_invitation' => 'Atmesti kvietimą'],
    'labels' => ['not_provided' => 'Nenurodyta', 'invitation_link' => 'Vienkartinė pasirašyta kvietimo nuoroda', 'reason' => 'Priežastis: :reason'],
    'empty' => ['title' => 'Organizacijų dar nėra', 'description' => 'Sukurkite organizaciją arba priimkite kvietimą.', 'members' => 'Narysčių nėra.', 'restrictions' => 'Aktyvių veiklos apribojimų nėra.'],
    'feedback' => ['created' => 'Organizacija sukurta.', 'invited' => 'Kvietimas sukurtas.', 'member_removed' => 'Nario prieiga pašalinta.', 'restricted' => 'Veiklos apribojimas pritaikytas.', 'suspended' => 'Organizacija sustabdyta.', 'invitation_accepted' => 'Kvietimas priimtas.', 'invitation_declined' => 'Kvietimas atmestas.'],
    'validation' => ['summary' => 'Patikrinkite organizacijos formos klaidas.', 'already_member' => 'Ši paskyra jau turi aktyvią narystę.', 'invitation_pending' => 'Šiai paskyrai jau yra galiojantis kvietimas.', 'invitation_unavailable' => 'Šis kvietimas nebegalioja.', 'idempotency_conflict' => 'Šis užklausos raktas jau priklauso kitam veiksmui.'],
    'audit' => ['created' => 'Organizacija sukurta', 'member_invited' => 'Narys pakviestas', 'invitation_accepted' => 'Kvietimas priimtas', 'invitation_declined' => 'Kvietimas atmestas', 'member_removed' => 'Narys pašalintas', 'restriction_applied' => 'Apribojimas pritaikytas', 'suspended' => 'Organizacija sustabdyta', 'factory' => 'Gamyklos audito įvykis'],
];
