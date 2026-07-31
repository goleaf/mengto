# Forum Source Prompt

This document is immutable implementation input. It preserves the exact
first-party forum prompt and its additive master extension as recovered from
the local Codex history. Future requirement changes must be appended as dated
revisions; earlier source text must never be silently edited or removed.

- Primary source timestamp: `1785397895`
- Additive extension timestamp: `1785445633`
- Combined raw payload SHA-256: `6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773`
- Checksum payload: exact primary prompt, two LF characters, exact extension prompt

## Source Part A: Original Forum Specification

<forum-source-primary>
# punkt 9 — forum s voprosami, otvetami, tematiceskimi obsuzhdenijami i bazoj poleznyx reshenij

## 1 — glavnaja ideja funkciji

forum dolzhen byt strukturirovannym mestom dlia dlitelnyx obsuzhdenij, voprosov, otvetov, instrukcij i poiskov reshenij

v otlicie ot obyknovennoj lenty, poleznaja tema na forume ne dolzhna bystro ischezat pod novymi publikacijami

kazhdyj vopros ili obsuzhdenie dolzhny imet

* poniatnyj zagolovok
* kategoriju
* tematiceskie metki
* sviazannogo pitomca
* avtora
* otvety
* kommentarii
* status
* poisk
* istoriju izmenenij
* nastrojki uvedomlenij
* vozmozhnost stat castiu bazy znanij

---

# raznica mezhdu forumom i drugimi razdelami

## 2 — forum i lenta

lenta podxodit dlia

* fotografij
* korotkix istorij
* obnovlenij
* video
* reakcii na tekuscije sobytija
* bystrogo obsenija

forum podxodit dlia

* podrobnyx voprosov
* dlitelnyx obsuzhdenij
* sravnenija raznyx reshenij
* instrukcij
* razbora konkretnyx slucaev
* nakoplenija poleznogo opyta
* poiskovyx zaprosov iz vneshnix poiskovyx sistem

---

## 3 — forum i gruppy

gruppa obedinjaet konkretnyx uchastnikov po interesu, porode ili mestu

forum mozhet byt globalnym i pozvoliat najti otvet nezavisimo ot togo, v kakix gruppax sostoit polzovatel

tema iz zakrytoj gruppy ne dolzhna avtomaticeski stanovitsia publicnoj temoj foruma

---

## 4 — forum i chat

chat podxodit dlia bystrogo dialoga v realnom vremeni

forum podxodit dlia informacii, kotoruju nuzhno

* strukturirovat
* najti pozhe
* pokazat drugim polzovateliam
* proverit
* dopolnit
* obnavliat
* prevratit v instrukciju

---

## 5 — forum i baza znanij

forum soderzhit zhivye voprosy, lichnyj opyt, raznye mnenija i obsuzhdenija

baza znanij dolzhna soderzhat obrabotannye, strukturirovannye i proverennye materialy

poleznaja tema foruma mozhet pozhe stat osnovoj dlia statji v baze znanij

---

# osnovnye zadaci foruma

## 6 — poisk otveta

pered sozdaniem novoj temy polzovatel dolzhen moc najti

* poxozhij vopros
* reshenie dlia takogo zhe vida zivotnogo
* obsuzhdenie po porode
* otvet specialista
* mestnuju rekomendaciju
* poshagovuju instrukciju
* nedavno obnovlennyj material

---

## 7 — polucenie raznyx tocek zrenija

v odnoj teme mogut otvecat

* obychnye xoziajie
* opytnye polzovateli
* volontery
* predstaviteli priutov
* proverennye specialisty
* organizacii
* moderatory

interfejs dolzhen jasno pokazyvat, kakim opytom ili statusom obladaet avtor otveta

---

## 8 — nakoplenie prakticeskogo opyta

forum dolzhen soxranjat poleznye realnye istorii

naprimer

* kak pitomec privykal k novomu domu
* kak xoziajin gotovil ego k poezdke
* kak reshal problemu s povodkom
* kak vybira l perederzhku
* kak organizoval vosstanovlenie posle operacii
* kak naxodil poterjannogo pitomca

lichnyj opyt dolzhen byt otmechen kak lichnyj opyt, a ne kak universalnaja professionalnaja instrukcija

---

## 9 — formirovanie soobscestva

forum mozhet pomogat polzovateliam

* znakomitsia
* podderzhivat drug druga
* delitsia opytom
* obsuzhdat slozhnye situacii
* naxodit mestnye reshenija
* pomogat novym xoziaevam
* povyshat kulturu otvetstvennogo otnoshenija k zivotnym

---

# struktura foruma

## 10 — glavnaja stranica

na glavnoj stranice foruma mozhno pokazat

* kategorii
* novye voprosy
* voprosy bez otvetov
* reshenne voprosy
* otvety specialistov
* popularnye obsuzhdenija
* mestnye temy
* obnovlennye instrukcii
* temy iz podpisok
* zakreplennye pravila
* poisk
* knopku zadat vopros

---

## 11 — osnovnye kategorii

forum mozhno razdelit na

* zdorovje
* pitanie
* povedenie
* obucenie
* uxod
* progulki
* puteshestvija
* dokumenty
* adopcija
* priuty
* poterjannye i najdennye zivotnye
* vyb or specialista
* veterinar nye kliniki
* gruming
* perederzhka
* transport
* gruppy i sobytija
* fotografija
* texnika i umnye ustrojstva
* memorialnaja podderzhka
* drugie temy

---

## 12 — kategorii po vidu zivotnogo

mozhno sozdat otdelnye razdely

* sobaki
* koski
* pticy
* gryzuny
* kroliki
* reptilii
* amfibii
* ryby
* loshadi
* selskoxoziajstvennye zivotnye
* ekzoticeskie zivotnye
* drugie zivotnye

---

## 13 — podkategorii po porode

vnutri kategorii mozhno filtrovat po porode

naprimer

* labrador
* nemetskaja ovcharka
* francuzskij buldog
* britanskaja koska
* mejn kun

forum ne dolzhen trebovat porodu dlia kazhdogo voprosa

dolzhny byt varianty

* bez porody
* metis
* poroda neizvestna
* vopros ne zavisit ot porody

---

## 14 — kategorii po vozrastu

temy mozhno sviazyvat s vozrastom

* novorozhdennyj
* shenok ili kotenok
* molodoj pitomec
* vzroslyj
* pozhiloj
* vozrast neizvesten

---

## 15 — mestnye razdely

mozhno sozdat razdely po

* strane
* gorodu
* rajonu
* konkretnomu mestu
* mestnoj organizacii

naprimer

* veterinarnye kliniki vilniusa
* progulki v kaunase
* priuty klaipedy
* pet friendly mesta v litve

---

## 16 — professionalnye razdely

dlia specialistov mogut byt otdelnye razdely

* veterinar naja praktika
* kinologija
* gruming
* zoopsixologija
* rabota priutov
* organizacija adopcii
* fotografija zivotnyx
* upravlenie klinikoj
* professionalnoe obucenie

dostup v nekotorye professionalnye obsuzhdenija mozhet trebovat proverku kvalifikacii

---

## 17 — zakrytye razdely

nekotorye temy mogut byt dostupny tolko

* uchastnikam gruppy
* proverennym specialistam
* volonteram organizacii
* sovladelcam konkretnogo pitomca
* uchastnikam kursa
* moderatoram

zakrytyj kontent ne dolzhen popadat v publicnyj poisk ili vneshnju indeksaciju

---

# tipy tem

## 18 — vopros

eto osnovnoj tip temy, gde avtor ishchet konkretnoe reshenie

tema mozhet imet

* zagolovok
* opisanie
* sviazannogo pitomca
* kategoriju
* metki
* media
* status
* prinjatyj otvet
* kommentarii
* obnovlenija avtora

---

## 19 — obsuzhdenie

obsuzhdenie ne trebuet odnogo pravilnogo otveta

naprimer

* kakie mesta vam nraviatsia dlia progulok
* kak vy organizujete puteshestvija
* kakie funkcii nuzhny umnomu oshejniku
* kak ulucshit mestnuju ploscadku
* kak pomogat novym volonteram

---

## 20 — prosba o rekomendacii

otdelnyj tip mozhet ispolzovatsia dlia poiska

* kliniki
* specialista
* grumera
* perederzhki
* korma
* mesta
* transporta
* oborudovanija
* gruppy
* sobytija

forma dolzhna zaprashivat gorod, vid pitomca i glavnye trebovanija

---

## 21 — razbor slucaja

avtor mozhet podrobno opisat situaciju

naprimer

* adaptacija pitomca iz priuta
* slozhnoe povedenie
* podgotovka k pereezdu
* vosstanovlenie posle operacii
* znakomstvo dvux zivotnyx
* konflikt na progulke

takaja tema mozhet obnovliatsia po etapam

---

## 22 — dnevnik reshenija

avtor mozhet vesti xronologiju

* nacalnaja problema
* cto bylo sdelano
* cto izmenilos
* cto ne pomoglo
* cto pomoglo
* itog

eta funkcija polezna dlia dlitelnyx processov, no ne dolzhna zamenjat medicinskuju kartu

---

## 23 — poshagovaja instrukcija

polzovatel ili specialist mozhet sozdat instrukciju

naprimer

* kak podgotovit pitomca k poezdke
* kak sozdat bezopasnoe mesto doma
* kak privyknut k perenoske
* kak organizovat pervuju vstrechu
* kak podgotovit dokumenty
* kak dejstvovat pri propazhe pitomca

---

## 24 — sravnenie

tema mozhet sravnivat

* dva tipa perenosok
* raznye gps oshejniki
* raznye vidy kormlenija
* kliniki
* straxovki
* tipy povodkov
* sposoby transportirovki
* uslugi

avtor dolzhen raskryt, esli on sviazan s rekomenduemym produktom ili biznesom

---

## 25 — opros

opros mozhet ispolzovatsia dlia

* poiska obschego mnenija
* vybora vremeni
* ocenki interesa
* sravnenija variantov
* planirovanija novoj funkcii
* vybora temy sobytija

rezultat oprosa ne dolzhen predstavliatsia kak professionalnoe ili nauchnoe issledovanie

---

## 26 — otkrytyj vopros ekspertu

proverennyj specialist mozhet sozdat sessiju

* zadavajte voprosy veterinaru
* voprosy kinologu
* voprosy grumeru
* voprosy priutu
* voprosy fotografu
* voprosy organizatoru vystavki

moderatory mogut sobirat voprosy i obediniat poxozhie

---

## 27 — novost ili obnovlenie

organizacija mozhet opublikovat

* izmenenie pravil
* novuju instrukciju
* obnovlenie procedury adopcii
* preduprezhdenie
* otchet
* izmenenie zakonnyx ili organizacionnyx trebovanij

takie temy dolzhny imet istochnik i datu obnovlenija

---

## 28 — tema podderzhki

temy emocionalnoj podderzhki mogut byt sviazany s

* utratoj pitomca
* dlitelnym lecheniem
* propazhej
* adaptaciej
* slozhnym resheniem
* vygoraniem volontera

dlia nix nuzhny bolee strogie pravila uvazhitelnogo obsenija

---

## 29 — srocnyj vopros

polzovatel mozhet ukazat, cto vopros srocnyj

no platforma dolzhna jasno objasnit, cto forum ne javliaetsia ekstrennoj sluzhboj

pri potencialno opasnyx simptomax nuzhno predlagat sviazatsia s klinikoj, a ne zhdat otveta foruma

---

## 30 — tema o propazhe pitomca

forum mozhet imet obsuzhdenie poiska, no osnovnoe objavlenie dolzhno byt strukturirovannoj kartockoj propazhi

v teme mozhno

* koordinirivat volonterov
* obsuzhdat nabludenija
* dobavliat obnovlenija
* zakreplyat kartu
* otmecat proverennye zony
* soobscit, cto pitomec najden

---

## 31 — anon imnaja tema

v nekotoryx razdelax polzovatel mozhet sozdat temu pod psevdonimom

eto mozhet byt polezno dlia

* emocionalnoj podderzhki
* slozhnyx semejnyx situacij
* konfliktov s organizaciej
* voprosov o povedenii
* voprosov o finansovoj slozhnosti lechenija

moderatory vse ravno dolzhny znat realnyj akkaunt avtora

---

# sozdanie voprosa

## 32 — poshagovaja forma

sozdanie voprosa mozhno razdelit na shagi

1. vybrat tip temy
2. ukazat kategoriju
3. vybrat pitomca
4. napisat zagolovok
5. podrobno opisat situaciju
6. dobavit fotografii ili video
7. ukazat, cto uze probovali
8. vybrat tematiceskie metki
9. nastroit privatnost
10. proverit predprosmotr
11. opublikovat

---

## 33 — vybor pitomca

avtor mozhet

* vybrat odnogo pitomca
* vybrat neskolkix
* sozdat vopros bez pitomca
* skryt imia pitomca
* pokazat tolko osnovnye dannye
* ne otkryvat profil

---

## 34 — avtomaticeskoe dobavlenie konteksta

s razreshenija avtora sistema mozhet dobavit

* vid
* porodu
* vozrast
* pol
* razmer
* stranu
* status sterilizacii, esli eto vazhno
* osnovnye osobennosti

medicinskie zapisi ne dolzhny prikreplyatsia avtomaticeski

---

## 35 — xoro shij zagolovok

sistema mozhet pomoch sdelat zagolovok poniatnym

ploxoj variant

`pomogite`

lucsij variant

`molodaja sobaka boitsia zaxodit v lift posle gromkogo zvuka`

zagolovok dolzhen soderzhat glavnuiu problemu bez izbytocnyx zaglavnyx simvolov i clickbait formulirovok

---

## 36 — proverka poxozhix tem

posle vvoda zagolovka sistema mozhet pokazat

* uzhe reshenne poxozhie voprosy
* sviazannye instrukcii
* otvety specialistov
* temy po takoj zhe porode
* mestnye obsuzhdenija

polzovatel mozhet otkryt poxozhuju temu ili vse ravno prodolzhit sozdanie novoj

---

## 37 — objasnenie raznicy

esli polzovatel prodolzhaet, mozhno sprosit

* cto v vashem slucae otlicaetsia
* pochemu sushestvujuscij otvet ne podxodit
* kakie novye obstojatelstva est

eto ulucshit kachestvo novoj temy i pomozhet ne sozdaivat pustye dublikaty

---

## 38 — strukturirovannoe opisanie

forma mozhet predlozhit polia

* cto proizoshlo
* kogda nacalos
* kak casto povtoriaetsia
* cto uze probovali
* cto izmenilos
* kak vedet sebia pitomec
* est li srochnye priznaki
* kakoj rezultat xochet poluchit avtor

---

## 39 — shablony voprosov

dlia raznyx kategorij mogut byt raznye shablony

naprimer dlia povedenija

* gde proisxodit
* kakie est triggeri
* kak dolgo dlitsia
* kak reagiruet xoziajin
* kakie metody probovali

dlia puteshestvija

* strana otpravlenija
* strana naznachenija
* vid transporta
* vid pitomca
* daty
* neobxodimye dokumenty

---

## 40 — medicinskij shablon

medicinskij vopros mozhet zaprashivat

* vid pitomca
* vozrast
* primernyj ves
* kogda nacalis simptomy
* est li appetit
* est li voda
* est li rvota
* est li diareja
* est li travma
* izmerialas li temperatura
* byl li kontakt s toksicnym veschestvom
* cto skazal veterinar, esli vizit uze byl

forma ne dolzhna samostojatelno stavit diagnoz

---

## 41 — srocnaja proverka pered publikaciej

esli tekst soderzhit potencialno opasnye priznaki, sistema mozhet pokazat zametnoe preduprezhdenie

naprimer

* trudnosti s dyxaniem
* silnoe krovotecenie
* poteria soznanija
* sudorogi
* podozrenie na otravlenie
* serioznaja travma
* nevozmozhnost mocitsia
* silnaja slabost

polzovatelju nuzhno predlozhit najti blizhaisuju kliniku i pozvonit, ne ozhidaja otvetov foruma

---

## 42 — mesto dlia obyazatelnogo utochnenija

sistema mozhet sprosit

`eto uzhe proverial veterinar`

varianty

* da
* net
* zapis uze zaplanirovana
* ne mogu sviazatsia
* ne otnositsia k medicinskoj teme

otvet ne dolzhen blokirovat publikaciju, no pomozhet drugim ponimat kontekst

---

## 43 — cto uze probovali

avtor dolzhen moc ukazat

* metody
* produkty
* trening
* vizit
* rekomendaciju specialista
* izmenenie sredy
* vremennye rezultaty

eto pomozhet ne polucat mnogo odinakovyx bespoleznyx otvetov

---

## 44 — zhelaemyj tip otveta

avtor mozhet ukazat

* lichnyj opyt
* professionalnoe mnenie
* mestnuju rekomendaciju
* poshagovuju instrukciju
* sravnenie variantov
* ssylki na istochniki
* emocionalnuju podderzhku

---

## 45 — fotografii

avtor mozhet dobavit

* odnu fotografiju
* neskolko fotografij
* podpis k kazhdoj
* alternativnyj tekst
* oznachenie cuvstvitelnogo kontenta
* zamazyvanie lichnyx dannyx
* obrezku

---

## 46 — video

video mozhet pokazat

* povedenie
* pohodku
* reakciu
* uslovija soderzhanija
* rabotu oborudovanija
* marshrut
* problemu s perenoskoj

video ne dolzhno avtomaticeski schitatsia dostatocnym dlia professionalnogo diagnoza

---

## 47 — dokumenty

mozhno prilozhit

* rezultat analiza
* instrukciju
* pravila transporta
* dokument organizacii
* raspisanie
* shemu
* dogovor

pered publikaciej nuzhno predupredit skryt

* polnoe imia
* adres
* telefon
* nomer dokumenta
* nomer mikrochipa
* platezhnye dannye
* lichnye dannye specialista bez neobxodimosti

---

## 48 — anonimnost pitomca

avtor mozhet skryt

* imia
* fotografiju
* tochnoe mesto
* porodu
* profil
* istoriju publikacij

pri etom mozhno ostavit tolko dannye, neobxodimye dlia ponimanija voprosa

---

## 49 — geograficeskaja privatnost

dlia mestnogo voprosa dostatocno ukazat

* stranu
* gorod
* rajon
* primernuju oblast

domashnij adres ne dolzhen publikovatsia

---

## 50 — auditorija

tema mozhet byt

* publicnoj
* dostupnoj zaregistrirovannym
* tolko podpisikam
* tolko uchastnikam gruppy
* tolko proverennym specialistam
* tolko po ssylke
* privatnym cernovikom

---

## 51 — pravila kommentirovanija

pered publikaciej avtor mozhet nastroit

* otvecat mogut vse
* tolko zaregistrirovannye
* tolko specialisty
* tolko uchastniki gruppy
* otvety proxodiat proverku
* novye otvety zakryty
* tema tolko dlia chtenija

---

## 52 — predprosmotr

pered publikaciej sistema dolzhna pokazat

* zagolovok
* kategoriju
* vidimye dannye pitomca
* media
* auditoriju
* lokaciju
* pravila otvetov
* cuvstvitelnye dannye, kotorye mogut byt raskryty

---

## 53 — avtomaticeskoe soxranenie cernovika

chernovik dolzhen soxraniatsia pri

* zakrytii vkladki
* propazhe interneta
* oshibke media
* perezagruzke
* perexode v drugoj razdel
* smene ustrojstva, esli sinxronizacija vkliuchena

---

## 54 — neskolko cernovikov

polzovatel mozhet imet

* lichnye cernoviki
* cernoviki organizacii
* cernoviki gruppy
* sovmestnye cernoviki redakcii
* cernoviki instrukcij

---

# publikacija i statusy temy

## 55 — statusy temy

tema mozhet imet status

* cernovik
* na proverke
* opublikovana
* trebuet utochnenija
* est otvety
* resheno
* castichno resheno
* otvet ne najden
* zakryto
* zablokirovano
* obedineno s drugoj temoj
* pereneseno
* arxivirovano
* udaleno
* obnovleno

---

## 56 — tema bez otvetov

vopros bez otvetov mozhet popadat v otdelnyj razdel

mozhno rekomendovat ego

* polzovateliam s podxodiascim opytom
* proverennym specialistam
* uchastnikam tematiceskix grupp
* mestnym polzovateliam
* xoziaevam poxozhix pitomcev

---

## 57 — zapros utochnenija

drugoj polzovatel ili moderator mozhet poprosit avtora utochnit

* vozrast
* vid
* mesto
* cto uze probovali
* kak dolgo dlitsia problema
* kakogo rezultata on ishchet
* est li fotografija
* byl li specialista

---

## 58 — obnovlenie avtora

avtor mozhet dobavit otdelnoe obnovlenie

naprimer

* byli u veterinara
* izmenili metod
* situacija ulucsilos
* problema vernulas
* nashli poterjannogo pitomca
* vybrali uslugu
* vopros bolshe neaktualen

obnovlenie dolzhno byt zametnee obyknovennogo kommentariia

---

## 59 — status resheno

avtor mozhet postavit status resheno i vybrat poleznyj otvet

pri etom tema ostajotsia dostupnoj dlia

* poisk a
* dopolnenij
* obnovlenij
* ispravlenija ustarevshix dannyx
* prevrashenija v material bazy znanij

---

## 60 — castichno resheno

esli odna cast voprosa reshen a drugaja net, avtor mozhet ukazat

* cto resheno
* cto esio neponiatno
* kakaja pomosc nuzhna
* kakie otvety uze byli polezny

---

## 61 — zakrytie temy avtorom

avtor mozhet zakryt novye otvety, esli

* vopros reshen
* pojavilos slishkom mnogo povtorov
* obsuzhdenie stalo emocionalno tiazhelym
* informacija bolshe neaktualna
* nuzhna pauza

moderator mozhet pereotkryt temu pri neobxodimosti

---

## 62 — avtomaticeskoe arxivirovanie

tema mozhet byt arxivirovana posle dlitelnogo otsutstvija aktivnosti

v arxive

* material dostupen dlia chtenija
* novye otvety mogut byt zakryty
* avtor mozhet zaprosit vozobnovlenie
* ustarevshaja informacija poluchaet metku

---

# otvety

## 63 — otvet i kommentarij

otvet dolzhen predlagat reshenie osnovnogo voprosa

kommentarij dolzhen

* utochniat otvet
* zadavat vopros
* obsuzhdat konkretnuju detal
* ispravliat netocnost

eto pomozhet ne prevrashat kazhduju korotkuju repliku v polnocennyj otvet

---

## 64 — struktura xoroshego otveta

redaktor mozhet predlozhit

* kratkij vyvod
* objasnenie
* poshagovye dejstvija
* cego izbegat
* kogda obrashatsia k specialistu
* lichnyj opyt
* istochniki
* ogranichenija otveta

---

## 65 — otvet ot imeni xoziajina

obychnyj polzovatel mozhet otvetit

* ot svoego imeni
* s ukazaniem opyta s konkretnym pitomcem
* s priviazkoj k svoej istorii
* anonimno, esli razdel eto razreshaet

---

## 66 — otvet specialista

proverennyj specialist mozhet imet metku

* veterinar
* kinolog
* grumer
* zoopsixolog
* predstavitel priuta
* drugoj specialist

riadom nuzhno pokazat, cto imenno provereno

---

## 67 — professionalnaja disklejmer

professionalnyj otvet mozhet soderzhat

* obschuju informaciju
* ogranichenie bez ochnogo osmotra
* rekomendaciju sviazatsia s mestnym specialistom
* priznaki, pri kotoryx nuzhna srocnaja pomosc
* region dejstvija pravil ili licenzii

---

## 68 — lichnyj opyt

otvet mozhno otmetit kak

* lichnyj opyt
* opyt volontera
* opyt organizacii
* professionalnoe mnenie
* perevod istochnika
* obobscenie obsuzhdenija

---

## 69 — istochniki

avtor otveta mozhet dobavit

* ssylku na oficialnyj material
* publikaciju
* instrukciju organizacii
* issledovanie
* dokument
* stranicu kliniki
* pravila transporta
* mestnye trebovanija

istochnik dolzhen byt sviazan s konkretnym utverzhdeniem

---

## 70 — metka bez istochnika

esli otvet delaet vazhnoe utverzhdenie bez istochnika, drugie polzovateli mogut zaprosit

* istochnik
* utochnenie
* razdelenie fakta i mnenija
* podtverzhdenie kvalifikacii

---

## 71 — prinjatyj otvet

avtor voprosa mozhet vybrat odin otvet kak naibolee poleznyj

eto oznachaet

* otvet pomog avtoru
* on zametno pokazyvaetsia
* tema mozhet polucit status resheno

eto ne dolzhno avtomaticeski oznachat absoliutnuju professionalnuju pravilnost

---

## 72 — neskolko poleznyx otvetov

dlia slozhnogo voprosa avtor mozhet otmetit neskolko otvetov

naprimer

* odin otvet pomog ponimat prichinu
* drugoj dal mestnyj kontakt
* tretij predlozhil poshagovyj plan

---

## 73 — otvet moderatora

moderator mozhet dobavit

* organizacionnoe utochnenie
* ssylku na pravila
* preduprezhdenie o bezopasnosti
* obobscenie
* ssylku na poxozhuju temu
* informaciju o perenose kategorii

moderator ne dolzhen avtomaticeski vydavat sebia za professionalnogo specialista

---

## 74 — obobscajuscij otvet

esli tema stala dlinn oj, moderator ili redaktor mozhet sozdat zakreplennyj itog

v nem mozhno ukazat

* cto sovpadalo v otvetax
* gde byli raznoglasija
* kakie istochniki polezny
* cto reshil avtor
* kakie voprosy ostalis

---

## 75 — sovmestnyj otvet

neskolko ekspertov mogut podgotovit odin otvet

nuzhno pokazat

* vsex avtorov
* roli
* kto redaktiroval
* istoriju versij
* kto podtverdil itog

---

## 76 — redaktirovanie otveta

avtor mozhet

* ispravit oshibku
* dobavit istochnik
* utochnit tekst
* dobavit obnovlenie
* ubrat lichnye dannye

riadom mozhno pokazat metku izmeneno

---

## 77 — istorija versij

dlia vazhnyx otvetov mozhno xranit

* predyduscij tekst
* vremia
* kratkoe opisanie izmenenija
* avtora izmenenija
* vosstanovlenie versii moderatorom

---

## 78 — udaleniie otveta

avtor mozhet udaliti otvet, esli on ne stal castiu oficialnogo materiala ili moderacionnogo razbiratelstva

pri nalichii poleznogo obsuzhdenija mozhno predlozhit

* anonimnizirovat avtora
* skryt lichnye dannye
* ostavit tekst
* peredat otvet redakcii s soglasiem

---

## 79 — otvet v vide video

avtor mozhet otvetit video, no nuzhno dobavit

* kratkoe tekstovoe opisanie
* subtitry
* rasshifrovku
* osnovnoj vyvod
* preduprezhdenie o cuvstvitelnom kontente

---

## 80 — otvet v vide audio

audio otvet dolzhen imet

* prodolzhitelnost
* rasshifrovku
* vozmozhnost izmenit skorost
* kratkoe opisanie
* dostupnost dlia poisk a po tekstu, esli rasshifrovka razreshena

---

# kommentarii pod otvetami

## 81 — naz nachenie kommentarev

kommentarii nuzhny dlia

* utochnenija
* korotkogo voprosa
* ispravlenija
* zaprosa istochnika
* blagodarnosti
* obsuzhdenija konkretnoj casti otveta

---

## 82 — vetki kommentarev

kommentarii mogut imet ogranichennuju vlozhennost

pri slishkom dlinnom obsuzhdenii sistema mozhet predlozhit

* sozdat otdelnuju temu
* prodolzhit v chate
* napisat polnocennyj otvet
* sozdat sovmestnyj material

---

## 83 — perenos kommentariia v otvet

avtor ili moderator mozhet preobrazovat poleznyj dlinn yj kommentarij v polnocennyj otvet

avtor dolzhen poluchit uvedomlenie i soxranit avtorstvo

---

## 84 — zakreplenie komentariia

avtor otveta ili moderator mozhet zakrepit vazhnoe utochnenie

naprimer

* obnovlenie
* ispravlenie
* dopolnitelnyj istochnik
* preduprezhdenie
* izmenenie pravil

---

## 85 — skrytie neotnosiascegosia obsuzhdenija

kommentarii, kotorye ushli ot temy, mozhno

* svernut
* perenesti v novuju temu
* skryt do nazhatija
* udaliti pri narushenii
* otmetit kak off topic

---

# golosovanie i poleznost

## 86 — polezno ili ne polezno

polzovateli mogut ocenivat otvet

* polezno
* ne pomoglo
* trebuet istochnika
* ustarelo
* opasno
* ne otnositsia k voprosu

negativnaja ocenka dolzhna byt sviazana s prichinoj, a ne ispolzovatsia tolko dlia lichnoj nepriaz ni

---

## 87 — golos za otvet

golos mozhet povyshat vidimost otveta, esli

* polzovatel realno procital ego
* akkaunt ne podozritelnyj
* net massovoj koordinacii
* otvet sootvetstvuet teme

---

## 88 — golos protiv

pri negativnom golose mozhno predlozhit

* netochnost
* net istochnika
* opasnyj sovet
* reklama
* ne otnositsia k teme
* povtor
* grubyj ton

---

## 89 — zhaloba ne ravna negativnomu golosu

esli otvet narushaet pravila ili mozhet pricinit vred, nuzhno ispolzovat zhalobu, a ne tolko snizit ego rejting

---

## 90 — kachestvo vazhnee popularnosti

otvet ne dolzhen stanovitcia pervym tolko izza bolshogo kolichestva reakcij

mozhno ucitivat

* sootvetstvie voprosu
* istochniki
* status avtora
* aktualnost
* bezopasnost
* prinjatie avtorom
* kachestvo objasnenija
* zhaloby
* priznaki nakrutki

---

## 91 — otdelnyj rejting dlia medicinskix otvetov

v medicinskix temax nuzhno prioritetno ucitivat

* professionalnuju proverku
* ostorozhnost formulirovok
* nalichie istochnikov
* otsutstvie opasnyx dozirovok
* rekomendaciju ochnogo vizita pri neobxodimosti
* aktualnost informacii

---

## 92 — nakrutka golosov

sistema dolzhna otslezhivat

* seti falshivyx akkauntov
* odinakovye dejstvija
* massovoe golosovanie iz odnoj gruppy
* koordinaciju protiv konkretnogo avtora
* pokupnye golosa
* avtomatizaciju

---

## 93 — brigading

esli tema prinesena iz drugoj gruppy dlia massovoj ataki, sistema mozhet

* vrem enno ogranichit golosa
* skryt schetciki
* vkliucit medlennyj rezhim
* zaprosit proverku
* ogranichit novye akkaunty

---

# reputacija polzovatelej

## 94 — reputacija za poleznyj vklad

polzovatel mozhet polucat reputaciju za

* poleznye otvety
* prinjatye reshenija
* kachestvennye instrukcii
* ispravlenie ustarevshix dannyx
* proverennye istochniki
* pomosc novym uchastnikam
* konstruktivnuju moderaciju

---

## 95 — reputacija ne dolzhna byt edin oj cifroj doverija

bolshaja reputacija ne oznachaet, cto polzovatel specialist vo vsex temax

lucshe pokazyvat opyt po kategorijam

naprimer

* opytnyj uchastnik po puteshestvijam
* poleznyj avtor po uxodu za koskami
* aktivnyj volonter
* proverennyj veterinar
* mestnyj ekspert po vilniusu

---

## 96 — professionalnyj status otdelno ot reputacii

professionalnaja kvalifikacija ne dolzhna prisvaivatsia tolko za mnogo otvetov

dlia nee nuzhna otdelnaja proverka

---

## 97 — znachki

mozhno vydavat

* pervyj poleznyj otvet
* desiat prinjatyx otvetov
* kachestvennyj istochnik
* pomogaet novym polzovateliam
* aktivnyj volonter
* avtor instrukcii
* redaktor bazy znanij
* proverennyj specialist
* mestnyj pomoschnik

---

## 98 — ogranichenie gejmifikacii

znachki ne dolzhny podtalkivat k

* massovym bespoleznym otvetam
* kopirovaniju
* konfliktam
* opasnym sovetam
* publikacii bez proverki
* golosovaniju po dogovoru

---

## 99 — prava po reputacii

posle dostizhenija opredelennogo urovnia mozhno razreshit

* predlagat metki
* pomogat naxodit dublikaty
* redaktirovat opечатki
* predlagat perenos kategorii
* uchastvovat v redakcionnoj proverke

kriticeskie moderacionnye prava ne dolzhny vydavatsia avtomaticeski tolko po cifre

---

## 100 — snizhenie reputacii

reputacija mozhet umenshatsia pri

* podtverzhdennom spame
* sistematiceskoj dezinformacii
* nakrutke
* plagiate
* oskorblenijax
* skrytoj reklame
* vydace sebia za specialista

odno oshibocnoe mnenie ne dolzhno avtomaticeski unichtozhat ves predyduscij vklad

---

# sortirovka tem

## 101 — novye

pokazyvajutsia po date sozdaniia

---

## 102 — aktivnye

pokazyvajutsia po poslednemu znachimomu otvetu

korotkaja reakcija ne dolzhna iskusstvenno podnimat temu beskonecno

---

## 103 — bez otvetov

otdelnyj razdel dlia tem, gde esio net ni odnogo polnocennogo otveta

---

## 104 — bez reshenija

tema mozhet imet mnogo otvetov, no avtor esio ne nashol podxodiascee reshenie

---

## 105 — reshenne

temy s prinjatym otvetom ili zakreplennym itogom

---

## 106 — otvety specialistov

temy, gde est otvet proverennogo specialista

eto ne dolzhno oznachat, cto drugie otvety skryty

---

## 107 — obnovlennye

temy, gde avtor dobavil vazhnoe obnovlenie

---

## 108 — mestnye

temy iz vybrannogo goroda ili regiona

---

## 109 — popularnye

popularnost mozhno rasscityvat po

* unikalnym prosmotram
* soxranenijam
* otvetam
* poleznym golosam
* dlitelnosti interesa
* kachestvu diskussii

---

## 110 — trendovye

trendovaja tema dolzhna byt aktivnoj za korotkij period, no ne obyazatelno samoj poleznoj

sistema ne dolzhna prodvigat opasnyj kontent tolko izza vysokoj aktivnosti

---

# poisk

## 111 — globalnyj poisk

polzovatel mozhet iskat po

* zagolovku
* tekstu
* otvetam
* kommentariiam
* metkam
* kategorijam
* pitomcam
* porode
* gorodu
* specialistu
* istochnikam
* statusu

---

## 112 — poisk obycnym jazykom

polzovatel mozhet napisat

* koska boitsia perenoski cto delat
* kakie dokumenty nuzhny dlia poezdki s sobakoj v polshu
* gde najti veterinara dlia popugaja v vilniuse
* kak poznakomit dvux vzroslyx koshek

sistema dolzhna pokazat, kak ona poniala zapros

---

## 113 — poisk s opечатkami

poisk dolzhen naxodit rezultat pri

* opечатke
* drugom padezhe
* raznom napisanii porody
* transliteracii
* drugom alfavite
* edinstvennom i mnozhestvennom chisle

---

## 114 — sinonimy

poisk mozhet ponimat sviaz

* veterinar i veterinar naja klinika
* koska i kot
* sobaka i pes
* perederzhka i sitter
* povodok i leash v perevedennom kontekste
* priiut i shelter

---

## 115 — filtry poiska

mozhno filtrovat

* vid pitomca
* porodu
* vozrast
* kategoriju
* status reshenija
* nalichie otveta specialista
* jazyk
* region
* period
* media
* istochniki
* minimalnoe kolichestvo poleznyx ocenok

---

## 116 — tocnaja fraza

polzovatel mozhet iskat tocnuiu posledovatelnost slov

eto polezno dlia

* nazvaniia lekarstva
* teksta oshibki ustrojstva
* nazvaniia dokumenta
* konkretnogo pravila
* modeli gps oshejnika

---

## 117 — poisk po avtoru

mozhno najti

* vse otvety specialista
* vse instrukcii organizacii
* temy konkretnogo polzovatelia
* materialy moderatora
* obnovlenija priuta

privatnye i anonimnye temy ne dolzhny raskryvatsia cerez poisk

---

## 118 — poisk po pitomcu

xoziajin mozhet najti vse svoi publicnye ili lichnye forum nye temy, sviazannye s konkretnym pitomcem

drugie polzovateli vidat tolko to, cto razresheno privatnostiu

---

## 119 — poisk po mestu

mozhno najti

* obsuzhdenija parka
* otzyvy o klinike
* voprosy o ploscadke
* sobytija v meste
* preduprezhdenija
* mestnye rekomendacii

---

## 120 — poisk bez rezultata

esli nichego ne najdeno, sistema mozhet

* predlozhit drugie slova
* ubrat cast filtrov
* pokazat poxozhie kategorii
* predlozhit zadat novyj vopros
* pokazat specialistov
* pokazat mestnye gruppy

---

## 121 — soxranennyj poisk

polzovatel mozhet soxranit zapros

naprimer

* novye voprosy o labradorax
* veterinar nye voprosy o pticax
* rekomendacii klinik vilniusa
* temy ob adopcii pozhilyx koshek

---

## 122 — uvedomlenija po poisku

pri pojavlenii novoj podxodiascej temy polzovatel mozhet polucit

* uvedomlenie v prilozhenii
* ezhednevnuju svodku
* ezhenedelnuiu svodku
* email
* nichego, tolko soxranennyj filtr

---

# tematiceskie metki

## 123 — glavnye metki

naprimer

* shenok
* pozhiloj pitomec
* adaptacija
* strax
* povodok
* puteshestvie
* klinika
* adopcija
* perederzhka
* korm
* gps
* poterjannoe zivotnoe
* memorialnaja podderzhka

---

## 124 — kontroliruemyj spravocnik

osnovnye metki dolzhny byt ed inymi dlia vsej platformy

eto pomozhet izbezhat dublikatov

naprimer

* labrador
* labradory
* labrador retriver

mogut byt sviazany s odnoj kanoniceskoj metkoj

---

## 125 — polzovatelskie metki

polzovatel mozhet predlozhit novuju metku

ona mozhet imet status

* predlozhena
* odobrena
* obed inena s sushestvujuscej
* otklonena
* vrem enno razreshena

---

## 126 — podpiska na metku

polzovatel mozhet podpisatsia na

* porodu
* temu
* gorod
* specializaciju
* tip voprosa
* konkretnuju problemu

---

## 127 — skrytie metki

polzovatel mozhet skryt

* cuvstvitelnye temy
* memorialnyj kontent
* medicinskie fotografii
* opredelennuju porodu
* politiceskie obsuzhdenija, esli oni voobse razresheny
* komerceskie rekomendacii

---

# sviazannye temy

## 128 — rekomendacii poxozhix voprosov

pod temoj mozhno pokazat

* poxozhie voprosy
* instrukcii
* otvety specialistov
* temy toj zhe porody
* mestnye obsuzhdenija
* bolee novye obnovlenija

---

## 129 — pocemu tema poxozha

mozhno objasnit

* ta zhe kategorija
* tot zhe vid pitomca
* poxozhij simptom
* ta zhe poroda
* tot zhe gorod
* poxozhee oborudovanie
* tot zhe dokument

---

## 130 — ne pokazyvat poxozhuju temu

polzovatel mozhet ukazat

* ne otnositsia
* drugoj vid zivotnogo
* drugaja problema
* informacija ustarela
* tema dubliruetsia nekorrektno

---

# dublikaty

## 131 — obnaruzhenie dublikata

sistema mozhet naxodit dublikaty po

* zagolovku
* tekstu
* kategorii
* metkam
* sviazannomu pitomcu
* nedavnim publikacijam
* odinakovym media

---

## 132 — predlozhenie pered publikaciej

pered sozdaniem novoj temy mozhno pokazat

`vozmozhno, otvet uzhe est zdes`

polzovatel mozhet

* otkryt temu
* podpisatsia
* dobavit kommentarij
* objasnit, pochemu ego slucaj drugoj
* prodolzhit sozdanie

---

## 133 — obedinenie dublikatov

moderator mozhet obedinit dve temy

nuzhno soxranit

* avtorov
* otvety
* kommentarii
* golosa
* prinjatye reshenija
* ssylki
* istoriju
* uvedomlenija podpiscikov

---

## 134 — kanoniceskaja tema

odna tema stanovitsia osnovnoj

starye ssylki dolzhny pereadresovyvat k nej ili pokazivat, kuda pereneseno obsuzhdenie

---

## 135 — razdelenie temy

esli odno obsuzhdenie soderzhit dve raznye temy, moderator mozhet razdelit ego

naprimer

* vopros o korme
* otdelnyj vopros o povedenii

avtory poluchajut uvedomlenie

---

# baza znanij

## 136 — glavnaja ideja

baza znanij dolzhna byt obrabotannoj biblioteko j poleznyx materialov

ona ne dolzhna kopirovat vse forum nye soobsenija bez redakcii

---

## 137 — istochniki materiala

statja mozhet byt sozdana na osnove

* reshennoj temy
* neskolkix poxozhix tem
* otveta specialista
* oficialnoj instrukcii
* materiala organizacii
* redakcionnogo issledovanija
* obsuzhdenija gruppy s soglasiem avtorov

---

## 138 — predlozhit temu v bazu znanij

polzovateli mogut predlozhit

* prinjatyj otvet
* poshagovuju instrukciju
* poleznoe obsuzhdenie
* mestnyj spravocnik
* sravnenie
* rezultat dlitelnogo razbora

---

## 139 — redakcionnaja proverka

pered publikaciej v baze znanij nuzhno proverit

* strukturu
* aktualnost
* istochniki
* bezopasnost
* avtorskie prava
* lichnye dannye
* konflikty interesov
* sootvetstvie kategorii
* jazyk

---

## 140 — struktura statji

statja mozhet soderzhat

* zagolovok
* kratkij otvet
* dlia kogo material
* osnovnoe objasnenie
* poshagovye dejstvija
* cego izbegat
* kogda nuzhen specialist
* castye oshibki
* sviazannye voprosy
* istochniki
* datu proverki
* avtorov i redaktorov

---

## 141 — korotkij vyvod v nachale

dlinnaja statja dolzhna nac inatsia s kratkogo prakticeskogo vyvoda

polzovatel mozhet bystro ponimat, podxodit li emu material

---

## 142 — urovni slozhnosti

material mozhet imet metku

* dlia nacinauscix
* srednij uroven
* dlia opytnyx xoziajev
* professionalnyj
* trebuet pomosci specialista

---

## 143 — tip materiala

* instrukcija
* spravocnik
* kontrolnyj spisok
* sravnenie
* faq
* razbor mifov
* podgotovka k sobytiju
* ekztrennaja pamjatka
* mestnyj spravocnik
* glossary

---

## 144 — kontrolnyj spisok

naprimer dlia poezdki

* dokumenty
* perenoska
* voda
* korm
* lekarstva
* adres kliniki
* pravila transporta
* kontakt mesta prozhivanija

polzovatel mozhet otmecat punkty lichno dlia sebia

---

## 145 — versii statji

pri obnovlenii nuzhno xranit

* staruju versiju
* novuju versiju
* datu
* cto izmenilos
* avtora
* prichinu obnovlenija

---

## 146 — data sledujuscej proverki

dlia informacii, kotoraia mozhet ustaret, nuzhno ustanovit datu proverki

naprimer

* pravila poezdki
* vremia raboty organizacii
* rekomendacii po oborudovaniju
* spisok dokumentov
* adresy klinik
* pravila sobytij

---

## 147 — metka ustarelo

esli material ne proverialsia dolgo ili pravila izmenilis, nuzhno pokazat

* informacija mozhet byt ustarevsh ej
* poslednjaja proverka
* kak predlozhit ispravlenie
* gde najti oficialnyj istochnik

---

## 148 — predlozhit ispravlenie

polzovatel mozhet ukazat

* cto netocno
* novaia informacija
* istochnik
* data
* kommentarij
* fotografija dokumenta bez lichnyx dannyx

---

## 149 — redaktory bazy znanij

roli mogut byt

* avtor
* redaktor
* ekspert proverki
* moderator
* perevodcik
* predstavitel organizacii
* glavn yj redaktor

---

## 150 — avtorstvo uchastnikov foruma

esli statja osnovana na otvetax polzovatelej, nuzhno ukazat ix vklad

varianty

* avtory
* pri uchastii
* osnovano na obsuzhdenii
* anonimnyj vklad po prosbe avtora

---

## 151 — kanoniceskij otvet

dlia casto povtoriajusc e gosia voprosa mozhno sozdat kanoniceskuju statju

novye poxozhie voprosy mogut ssylatsia na nee, no polzovatel vse ravno mozhet sozdat temu pri osobom slucae

---

## 152 — sviaz statji s forumom

v statje mozhno pokazat

* obsuzhdenija
* poslednie voprosy
* obnovlenija
* otvety specialistov
* predlozhenija ispravlenij
* istoriju, na osnove kotoroj ona sozdana

---

## 153 — obsuzhdenie statji

vmesto izmenenija osnovnogo teksta v kommentarijax mozhno sozdat otdelnuju vetku

v nej polzovateli predlagajut

* utochnenija
* novye istochniki
* ispravlenija
* lokalnye iskliucenija
* primery

---

## 154 — mestnye versii statji

odna tema mozhet imet raznye versii dlia

* stran
* gorodov
* zakonnyx trebovanij
* transportnyx sistem
* klimata
* dostupnyx uslug

---

## 155 — materialy dlia pecati

polzovatel mozhet podgotovit udobnuju versiju

* bez lishnej navigacii
* s kontrolnym spiskom
* s qr kodom na aktualnuju versiju
* s datoj poslednego obnovlenija
* s osnovnymi kontaktami
* s preduprezhdenijami

---

## 156 — soxranenie oflajn

vazhnye instrukcii mozhno soxranit

* dlia poezdki
* dlia ekstrennoj situacii
* dlia perederzhki
* dlia sobytija
* dlia propazhi pitomca

pri otkrytii oflajn nuzhno pokazat, kogda material byl obnovlen

---

# ekspertnoe uchastie na forume

## 157 — proverennyj specialist

specialist dolzhen imet

* podtverzhdennuju lichnost
* specializaciju
* region kvalifikacii
* status dokumentov
* profil
* pravila professionalnogo obsenija

---

## 158 — oblasti kompetencii

specialist mozhet ukazat

* s kakimi vidami rabotaet
* po kakim temam otvecaet
* cego ne konsultiruet
* est li onlajn konsultacii
* jazyki
* region

---

## 159 — otvet vne kompetencii

proverennyj veterinar ne dolzhen avtomaticeski vydavat sebia za kinologa, grumera ili jurista

interfejs mozhet pokazat

`status proverki ne otnositsia k etoj teme`

---

## 160 — konflikty interesov

specialist dolzhen raskryt, esli

* rekomenduet svoj servis
* rabotaet s brendom
* poluchil tovar
* javliaetsia partnerom kliniki
* prodajot kurs
* predstavliaet organizaciju

---

## 161 — reklama specialista

specialist mozhet ukazat svoi uslugi v profile

no otvet ne dolzhen byt tolko reklamoj bez poleznoj informacii

---

## 162 — sessii voprosov i otvetov

specialist mozhet provesti ogranichennuju po vremeni sessiju

funkcii

* sbor voprosov
* golosovanie za interesnye
* moderacija
* raspisanie
* priamoj efir
* tekstovye otvety
* itogovaja statja
* arxiv

---

## 163 — otvet organizacii

klinika, priut ili kompaniia mozhet otvecat oficialno

riadom dolzhno byt ukazano

* organizacija
* sotrudnik
* rol
* status proverki
* data

---

## 164 — otvet ot imeni brenda

takoi otvet dolzhen imet metku oficialnyj predstavitel

on ne dolzhen vygljadet kak nezavisimyj otzyv obycnogo polzovatelia

---

# med icinskaja bezopasnost

## 165 — forum ne stavit diagnoz

na medicinskix stranicax nuzhno jasno pokazat

* forum ne zameniaet veterinara
* otvet mozhet byt netocnym
* udalennyj sovet ne uc ityvaet polnyj osmotr
* pri srochnyx simptomax nuzhna klinika
* ne nado samostojatelno menjat naznacenie

---

## 166 — opasnye dozirovki

nuzhno osobenno proveriat otvety s

* konkretnymi dozami
* preparatami dlia liudej
* neproverennymi smesiami
* otmenoj lekarstva
* kombinaciej preparatov
* rekomendaciej bez vesa i diagnoza

---

## 167 — toksicnye veschestva

sistema mozhet zametat upominanija

* bytovoj ximii
* yadov
* lekarstv dlia liudej
* opasnyx produktov
* efirnyx masel
* yadovityx rastenij
* neproverennyx dobavok

pri risk e nuzhno pokazivat preduprezhdenie, a ne avtomaticeskij diagnoz

---

## 168 — medicinskaja dezinformacija

priznaki mogut byt

* garantiruet izleczenie
* zapreshchaet veterinarov
* prodajot sekretnyj metod
* utverzhdaet, cto odno sredstvo lecit vse
* ne ukazyvaet istochnik
* prizyvaet skryt informaciju ot vracha
* prosit otmenit proverennuju terapiju

---

## 169 — otvety po fotografii

nuzhno napomnit

* fotografija mozhet iskhazhat cvet
* ne vidny vse simptomy
* net palp acii
* net analizov
* net polnoj istorii
* nuzhen ochnyj osmotr pri somnenii

---

## 170 — avtomaticeskoe srochnoe preduprezhdenie

esli forum raspoznaet potencialno opasnyj tekst, on mozhet pokazat

* pozvonite v kliniku
* ne zhdi te otvetov
* vozmite upakovku vozmozhnogo toksina
* ne vyzyvajte rvotu bez instrukcii specialista
* najdite blizhaisuju pomosc

konkretnye rekomendacii dolzhny byt ostorozhnymi i ne zamenjat professionalnuju pomosc

---

## 171 — zakrytie opasnoj vetki

moderator mozhet vrem enno zakryt otvety, esli

* rasprostraniajutsia opasnye sovety
* avtoru nuzhna srocnaja pomosc
* nachalas massovaja dezinformacija
* nuzhna professionalnaja proverka

---

## 172 — ekspertnaia proverka posle publikacii

opasnyj ili spornyj otvet mozhet polucit status

* proveriaetsia
* trebuet istochnika
* ne rekomenduetsia
* udaleno izza riska
* ispravleno avtorom
* provereno specialistom

---

# povedenie i obucenie

## 173 — bez universalnyx garantij

otvety ne dolzhny obescat

* problema ischeznet za odin den
* odin metod podxodit vsem
* nakazanie vsegda rabotaet
* konkretnaja poroda vsegda vedet sebia odinakovo

---

## 174 — opisanie konteksta povedenija

poleznyj vopros dolzhen po vozmozhnosti ukazyvat

* gde proisxodit
* pri kakix triggerax
* kak casto
* kak dolgo
* kak reagiruet xoziajin
* cto bylo do situacii
* est li bolezn ili bol
* izmenilas li sreda

---

## 175 — opasnye metody

moderacija dolzhna obrashat vnimanie na

* zhestokoe nakazanie
* udushenie
* udary
* namerennoe zapugivanie
* prinuzhdenie k opasnomu kontaktu
* lishenie vody
* opasnye elektro ustrojstva
* metody, vyzyvajuscie silnyj stress

---

## 176 — individualnyj specialist

pri serioznoj agressii, straxe ili risk e forum mozhet predlozhit

* veterinarnyj osmotr
* proverennogo kinologa
* zoopsixologa
* bezopasnyj plan do konsultacii
* upravlenie distanciej
* iskliuchenie riskov

---

# adopcija i priuty

## 177 — voprosy po adopcii

temy mogut byt

* podgotovka doma
* znakomstvo s pitomcem
* anketa
* adaptacija
* sovmestimost s drugimi zivotnymi
* dokumenty
* pervye dni
* vozvrat v priut
* podderzhka posle adopcii

---

## 178 — zascita ot nelegalnoj prodazhi

forum ne dolzhen ispolzovatsia dlia skrytoj prodazhi zivotnyx pod vidom obsuzhdenija

podozritelnye priznaki

* cena v licnyx soobsenijax
* massovye pomet y
* otsutstvie proverki
* poddelnye dokumenty
* srocnaja predoplata
* otkaz pokazat uslovija soderzhanija

---

## 179 — proverka organizacii

oficialnyj otvet priuta dolzhen byt otmechen kak predstavitel organizacii

obychnyj polzovatel ne dolzhen vydavat sebia za priut bez proverki

---

# rekomendacii produktov i uslug

## 180 — razdelenie opyta i reklamy

otvet mozhet byt otmechen

* kupil sam
* poluchil besplatno
* partnerskaja ssylka
* rabotaju v etoj kompanii
* prodaju etu uslugu
* reklama

---

## 181 — zapret skrytoj reklamy

nelzia massovo otvecat na raznye voprosy odnoj i toj zhe reklamnoj ssylkoj bez realnoj polzy

---

## 182 — strukturirovannaja rekomendacija

poleznyj otvet mozhet ukazat

* dlia kakogo pitomca podxodit
* pliusy
* minusy
* cenu
* gde ispolzovalsia
* kak dolgo
* est li konflikt interesov
* alternativy

---

## 183 — aktualnost cen

ceny i nalichie mogut meniatcia

riadom mozhno ukazat datu, kogda avtor pokupal ili proverial informaciju

---

# privatnost

## 184 — skrytie profilia iz temy

avtor mozhet opublikovat temu bez ssylki na publicnyj profil pitomca

moderatory vse ravno dolzhny znat akkaunt dlia bezopasnosti

---

## 185 — anonimnoe imia

vmesto osnovnogo imeni mozhet pokazyvatsia

* anonimnyj xoziajin
* anonimnyj volonter
* anonimnyj uchastnik gruppy

---

## 186 — skrytie istorii

anonimnaja tema ne dolzhna pokazivat v publicnom profile

* drugie temy avtora
* ego pitomcev
* gorod
* druzej
* organizacii

---

## 187 — lichnye dannye v tekste

sistema mozhet predupredit pri publikacii

* telefona
* email
* polnogo adresa
* nomera dokumenta
* nomera mikrochipa
* bankovskix dannyx
* kodov podtverzhdenija
* lichnyx dannyx rebenka

---

## 188 — redaktirovanie moderatorom lichnyx dannyx

pri srochnom risk e moderator mozhet zamazat ili udaliti lichnye dannye bez udaleniia vsej poleznoj temy

dejstvie dolzhno byt zapisano v istorii

---

## 189 — udaleniie svoej temy

avtor mozhet zaprosit

* polnoe udalenie
* anonimnizaciju
* skrytie profilia
* udalenie media
* zakrytie kommentarev
* udaleniie lichnyx dannyx

---

## 190 — tema s poleznymi otvetami

esli tema uze imeet poleznye otvety, polnoe udalenie mozhet razrushit bazu znanij

mozhno predlozhit

* anonimnizaciju avtora
* udalenie lichnogo opisania
* soxranenie obschego voprosa
* skrytie sviazi s profilem

---

## 191 — nesovershennoletnie

dlia polzovatelej mladse dopustimogo vozrasta nuzhny

* zakrytaja privatnost
* ogranichenie lichnyx kontaktov
* filtr licnyx dannyx
* zapret tocnogo mesta
* prioritetnaja moderacija
* semejnoe upravlenie

---

# uvedomlenija

## 192 — podpiska na temu

polzovatel mozhet polucat

* vse otvety
* tolko otvety specialistov
* tolko obnovlenija avtora
* tolko upominanija
* tolko prinjatyj otvet
* ezhednevnuju svodku
* bez uvedomlenij

---

## 193 — avtomaticeskaja podpiska avtora

avtor po umolcaniju mozhet polucat uvedomlenija ob otvetax

on dolzhen moc izmenit eto v ljuboe vremia

---

## 194 — podpiska bez otveta

polzovatel mozhet sledit za temoj, ne ostavljaja kommentarij

---

## 195 — podpiska na kategoriju

naprimer

* novye voprosy po povedeniju
* voprosy o pticax
* mestnye rekomendacii
* voprosy bez otvetov
* srocnye zaprosy volonterov

---

## 196 — podpiska na avtora

polzovatel mozhet polucat novye

* instrukcii
* otvety
* professionalnye materialy
* sessii voprosov
* obnovlenija

---

## 197 — gruppirovka uvedomlenij

vmesto mnogix otdelnyx soobsenij mozhno pokazat

`v vashej teme pojavilos 8 otvetov, odin ot specialista i dva utochniauscix voprosa`

---

## 198 — tixie casy

obycnye forum nye uvedomlenija ne dolzhny narushat tixie casy

iskliucenijami mogut byt

* otvet po aktivnomu poisku pitomca
* preduprezhdenie moderacii
* bezopasnost akkaunta
* srocnoe obnovlenie organizacii, esli polzovatel soglasilsia

---

## 199 — napominanie otvetit

polzovatel mozhet otmetit temu

* otvetit pozhe
* proverit zavtra
* dobavit obnovlenie cerez nedeliu
* sprosit specialista
* zakryt posle reshenija

---

## 200 — napominanie avtora ob itoge

esli v teme est otvety, no net obnovlenija, sistema mozhet cherez vremia sprosit

* pomog li kakoi-to otvet
* reshen li vopros
* mozhno li vybrat poleznyj otvet
* nuzhna li esio pomosc

---

# moderacija

## 201 — roli moderacii

mogut byt

* moderator kategorii
* globalnyj moderator
* ekspert po bezopasnosti
* medicinskij moderator
* moderator mestnyx tem
* anti spam moderator
* glavn yj administrator
* apelliacionnyj moderator

---

## 202 — moderacionnaja ochered

moderator vidit

* novye zhaloby
* opasnye medicinskie otvety
* spam
* podozritelnye ssylki
* zhestokoe obrashenie
* lichnye dannye
* dublikaty
* konfl ikty
* podozritelnye akkaunty
* materialy na proverke

---

## 203 — prioritet

vysokij prioritet mogut imet

* realnaja ugroza
* zhestokoe obrashenie
* opasnyj medicinskij sovet
* otravlenie
* moshennicestvo
* kontakt s nesovershennoletnim
* raskrytie adresa
* prodazha zapresennogo
* koordinirovannoe presledovanie

---

## 204 — zhaloba na temu

priciny

* spam
* ne ta kategorija
* dublikat
* opasnyj sovet
* medicinskaja dezinformacija
* zhestokoe obrashenie
* moshennicestvo
* lichnye dannye
* oskorblenija
* skrytaja reklama
* nelegalnaja prodazha
* narushenie avtorskix prav
* drugaja pricina

---

## 205 — zhaloba na otvet

polzovatel mozhet vybrat konkretnyj otvet i ukazat prichinu

moderator dolzhen videt vopros i sosednie kommentarii dlia konteksta

---

## 206 — zhaloba na profil

esli problema sviazana ne s odnim soobsenijem, a s sistematiceskim povedeniem, mozhno pozhalovatsia na profil

---

## 207 — zhaloba na eksperta

dopolnitelnye priciny

* vydajot sebia za drugogo specialista
* kvalifikacija ne podtverzhdena
* opasnye rekomendacii
* skrytaja reklama
* konflikt interesov
* diagnoz bez osnovanija
* prodazha poddelnyx uslug

---

## 208 — dejstvija moderatora

moderator mozhet

* nichego ne delat
* perenesti kategoriju
* izmenit metki
* poprosit utochnenie
* skryt lichnye dannye
* zakryt temu
* perenesti v dublikat
* ob edinit
* razdelit
* skryt otvet
* udaliti
* ogranichit avtora
* otpravit preduprezhdenie
* peredat slucaj specialnoj komande

---

## 209 — myagkoe preduprezhdenie

naprimer

* dobavte istochnik
* razdelite lichnyj opyt i professionalnyj sovet
* ubrat reklamu
* ne publikujte adres
* izmenite ton
* utochnite, cto nuzhen specialist

---

## 210 — vrem ennoe skrytie

kontent mozhet byt skryt do proverki, esli est risk

* vreda
* moshennicestva
* raskrytija dannyx
* zhestokogo obrashenia
* massovoj travli
* nezakonnogo kontenta

---

## 211 — zakrytie temy

moderator mozhet zakryt temu pri

* massovom konflikte
* povtorenii
* reshenii voprosa
* neaktualnosti
* narushenii
* neobxodimosti proverki
* ugroze avtoru

---

## 212 — medlennyj rezhim

v konfliktnoj teme mozhno ustanovit

* odin otvet v piat minut
* odin otvet v cas
* predvaritelnuju proverku novyx akkauntov
* zapret media
* zapret ssylok
* tolko proverennye uchastniki

---

## 213 — zasciscennaja tema

v vazhnoj teme mozhno razreshit otvety tolko polzovateliam s minimalnym urovnem doverija ili podtverzhdeniem

---

## 214 — perenos v druguju kategoriju

staraia ssylka dolzhna prodolzhat rabotat

avtor poluchaet uvedomlenie, pochemu tema perenesena

---

## 215 — obedinenie

pered obedineniiem moderator dolzhen proverit

* odinakov li vopros
* ne poteriaetsia li vazhnyj kontekst
* ne narushaetsia li privatnost
* sovpadajut li auditorii
* est li prinjatye otvety

---

## 216 — anonimnaja zhaloba

avtor kontenta ne dolzhen videt lichnost zhalobscika

---

## 217 — objasnenie reshenija

polzovatel dolzhen polucit

* kakoe pravilo narusheno
* kakoj kontent
* kakoe dejstvie primeneno
* srok
* mozhno li ispravit
* mozhno li podati apellaciju

---

## 218 — apellacija

polzovatel mozhet

* vybrat reshenie
* napisat objasnenie
* prilozhit istochnik
* pokazat kontekst
* zaprosit povtornuju proverku

po vozmozhnosti apellaciju dolzhen rassmatrivat drugoj moderator

---

## 219 — istorija moderacii

nuzhno zapisivat

* kto rassmatrival
* kogda
* cto sdelano
* po kakomu pravilu
* kakie dokazatelstva
* byla li apellacija
* bylo li reshenie izmeneno

---

## 220 — publicnaja metka moderacii

esli tema izmenena moderatorom, mozhno pokazat

* pereneseno
* zakryto
* obedineno
* lichnye dannye skryty
* opasnyj otvet udal en
* obsuzhdenie obnovleno

vnutrennie konfidencialnye detali ne dolzhny publikovatsia

---

# zascita ot spama

## 221 — ogranichenija novyx akkauntov

novyj polzovatel mozhet vrem enno imet

* limit tem
* limit otvetov
* zapret mnogix ssylok
* proverku pervoj publikacii
* limit upominanij
* limit lichnyx reklamnyx predlozhenij

---

## 222 — odinakovye otvety

sistema dolzhna zametat, esli polzovatel kopiruet odin i tot zhe tekst vo mnogie temy

---

## 223 — massovaja reklama

priznaki

* odna ssylka vo vsex otvetax
* net otveta na vopros
* odinakovye frazy
* skrytoe prodvizhenie
* falshivye rekomendacii
* mnogo akkauntov odnogo biznesa

---

## 224 — avtomaticeskie akkaunty

mozhno otslezhivat

* slishkom bystruju aktivnost
* odinakovye intervaly
* massovoe golosovanie
* avtomaticeskie frazy
* odinakovye media
* sotni tem za korotkoe vremia

---

## 225 — podozritelnye ssylki

sistema mozhet

* proverit adres
* pokazat domen
* predupredit
* zablokirovat sokrashennuju ssylku
* otpravit na proverku
* skryt predprosmotr

---

## 226 — falshivye sbory deneg

forum ne dolzhen pozvoliat neproverennye sbory pod vidom voprosa

dlia sbora nuzhna otdelnaia proveriaemaja forma s

* organizatorom
* celju
* poluchatelem
* dokumentami
* otchetnostiu
* statusom proverki

---

# konfliktnoe obsenie

## 227 — uvazhitelnyj ton

pravila dolzhny zapreshat

* lichnye oskorblenija
* unizhenie
* nasmeshki nad novickom
* travliu za porodu pitomca
* obvinenija bez dokazatelstv
* ugrozy
* raskrytie lichnyx dannyx

---

## 228 — kritika idei, a ne celoveka

polzovatel mozhet napisat

`etot metod mozhet byt opasnym izza takix-to prichin`

no ne dolzhen perehodit na oskorblenie avtora

---

## 229 — ohlazhdenie konflikta

moderator mozhet

* napomnit pravila
* zamorozit vetku
* vkliucit medlennyj rezhim
* predlozhit raznye temy
* skryt lichnye napadki
* zakryt obsuzhdenie

---

## 230 — nepublicnoe preduprezhdenie

pri pervom neznacitelnom narushenii mozhno otpravit lichnoe objasnenie bez publicnogo kleyma

---

## 231 — povtoriajusciesia narushenija

mery mogut usilivatsia

* preduprezhdenie
* ogranichenie
* proverka pered publikaciej
* vremennyj ban
* zapret konkretnogo razdela
* polnaja blokirovka

---

# zhestokoe obrashenie

## 232 — prioritetnaja zhaloba

pri podozrenii na zhestokoe obrashenie mozhno

* soxranit dokazatelstva
* skryt cuvstvitelnyj kontent
* peredat specialnoj moderacii
* zascitit zhalobscika
* pokazat mestnye kontakty pomosci
* ne preduprezhdat avtora slishkom rano pri risk e udaleniia dokazatelstv

---

## 233 — avtomaticeskoe prodvizhenie zapreseno

video ili tema s zhestokim obrasheniem ne dolzhny stanovitsia trendovymi tolko izza mnogix reakcii

---

## 234 — obrazovatelnyj kontekst

material mozhet byt dopustim, esli on

* dokumentiruet problemu
* pomogaet spaseniu
* publik uetsia s cuvstvitelnym preduprezhdeniem
* ne romantiziruet zhestokost
* ne pokazivaet lishnie graficeskie detali

---

# mnogoiazychnost

## 235 — osnovnoj jazyk temy

avtor ukazyvaet jazyk ili sistema opredeliaet ego avtomaticeski

avtor dolzhen moc ispravit oshibku

---

## 236 — avtomaticeskij perevod

polzovatel mozhet

* perevesti zagolovok
* perevesti vopros
* perevesti otvet
* pokazat original
* soobscit ob oshibke
* otkliucit perevod

---

## 237 — professionalnye terminy

nazvanija

* lekarstv
* porod
* dokumentov
* organizacij
* modelej ustrojstv
* adresov

ne dolzhny iskhazhatsia avtomaticeskim perevodom

---

## 238 — perevod medicinskix otvetov

nuzhno jasno ukazyvat

* perevod avtomaticeskij
* vozmozhny oshibki
* original dostupen
* pri vazhnom reshenii nuzhno utochnit u specialista

---

## 239 — mnogojazycnaja tema

avtor mozhet dobavit rucnoj perevod zagolovka i osnovnogo teksta

otvety mogut ostavatsia na raznyx jazykax s knopkoj perevoda

---

## 240 — edinaja kategoriia dlia raznyx jazykov

temy ne dolzhny razdeliatsia na dublikaty tolko izza jazyka

naprimer kanoniceskaja metka porody dolzhna byt odnoj, no otobrazhatsia na jazyke interfejsa

---

## 241 — jazykovye filtry

polzovatel mozhet vybrat

* tolko moi jazyki
* pokazyvat vse s perevodom
* ne pokazyvat opredelennyj jazyk
* prioritizirovat mestnyj jazyk
* pokazyvat original i perevod

---

# dostupnost

## 242 — ekran nye diktory

tema dolzhna imet logiceskuju strukturu

* zagolovok
* avtor
* status
* kategorija
* vopros
* otvety
* kommentarii
* upravliajuscije knopki

---

## 243 — alternativnyj tekst

fotografii dolzhny podderzhivat opisanie

avtomaticeskoe opisanie mozhno predlozhit, no avtor dolzhen moc ego ispravit

---

## 244 — dostupnost video

video dolzhno imet

* subtitry
* rasshifrovku
* opisanie
* upravlenie klaviaturoj
* vozmozhnost ostanovit avtomaticeskoe vosproizvedenie

---

## 245 — cvet ne edinstvennyj signal

statusy

* resheno
* srochno
* ekspertnoe
* opasno
* zakryto
* obnovleno

dolzhny imet tekst i ikonku

---

## 246 — uvelichenie teksta

interfejs ne dolzhen lomatsia pri uvelichenii

dlinnye zagolovki dolzhny perenositcia, a knopki ostavatsia dostupnymi

---

## 247 — upravlenie klaviaturoj

nuzhno podderzhivat

* poisk
* filtry
* otkrytie temy
* perehod po otvetam
* napisanie otveta
* reakcii
* zakladki
* zhalobu
* perevod
* zakrytie okon

---

## 248 — umenshenie animacij

polzovatel mozhet otkliucit

* animirovannye reakcii
* avtomaticeskuju prokrutku
* pulsirujuscije metki
* rezkie perexody
* avtomaticeskoe vosproizvedenie media

---

# mobilnaja versija

## 249 — glavnaja stranica na telefone

nado prioritetno pokazat

* poisk
* knopku zadat vopros
* podpiski
* bez otvetov
* kategorii
* temy dlia vas
* otvety specialistov
* mestnye temy

---

## 250 — kartocka temy

kartocka mozhet soderzhat

* zagolovok
* kategoriju
* vid pitomca
* status
* kolichestvo otvetov
* nalichie prinjatogo otveta
* nalichie otveta specialista
* vremia poslednej aktivnosti
* avatara avtora

---

## 251 — bystryj otvet

na telefone polzovatel mozhet

* napisat tekst
* dobavit fotografiju
* zapisat audio
* prilozhit ssylku
* soxranit cernovik
* posmotret predprosmotr

---

## 252 — soxranenie pozicii

posle otkrytija profilia, ssylki ili media polzovatel dolzhen vernutsia k tomu zhe mestu v teme

---

## 253 — dlinnye temy

mozhno pokazat navigaciju

* k pervomu otvetu
* k prinjatomu
* k novym
* k otvetam specialista
* k obnovleniju avtora
* v konec

---

## 254 — slaboe soedinenie

pri medlennom internete sistema mozhet

* snachala zagruzhat tekst
* skryvat media do nazhatija
* soxranjat cernovik
* povtoriat otpravku
* pokazivat keshirovannuju temu
* ne zagruzhat video avtomaticeski

---

# versija dlia kompiutera

## 255 — struktura ekrana

na bolshom ekrane mozhno pokazat

* kategorii sleva
* temu po centru
* sviazannye materialy sprava
* poisk
* status
* zakreplennye otvety
* navigaciju po dlinn oj vetke

---

## 256 — rezhim citatelia

mozhno skryt

* bokovye paneli
* rekomendacii
* reakcii
* lishnie elementy

i ostavit tolko vopros, otvety i istochniki

---

## 257 — sravnenie otvetov

polzovatel mozhet otkryt dva ili neskolko otvetov riadom

eto polezno dlia

* sravnenija metodov
* produktov
* uslug
* mnenij specialistov
* mestnyx variantov

---

# lichnye funkcii

## 258 — zakladki

polzovatel mozhet soxranit

* temu
* otvet
* kommentarij
* instrukciju
* istochnik
* specialista

---

## 259 — kollekcii

naprimer

* obucenie baksa
* poezdka v polshu
* uxod za lunoj
* kliniki
* idei dlia doma
* memorialnaja podderzhka
* materialy dlia volonterov

---

## 260 — lichnye zametki

riadom s temoj polzovatel mozhet ostavit privatnuju zametku

naprimer

* sprosit veterinara
* kupit perenosku
* proverit dokumenty
* poprobovat posle konsultacii
* vernutsia cerez nedeliu

---

## 261 — istorija prosmotra

polzovatel mozhet najti nedavno prosmotrennye

* temy
* otvety
* statji
* specialistov
* kategorii

istoriju mozhno ocistit ili otkliucit

---

## 262 — moi temy

razdel mozhet soderzhat

* opublikovannye
* cernoviki
* s novymi otvetami
* bez otvetov
* reshenne
* zakrytye
* anonimnye
* na proverke
* udalennye s vozmozhnostiu vosstanovlenija

---

## 263 — moi otvety

mozhno filtrovat

* prinjatye
* poleznye
* s novymi kommentariami
* trebuet istochnika
* na moderacii
* obnovlennye
* po kategorii

---

# integracija s profilem pitomca

## 264 — forum nye temy v profile

xoziajin mozhet vybrat, pokazivat li

* publicnye voprosy
* instrukcii
* poleznye otvety
* temy o dostizhenijax
* razbory
* lichnyj opyt

medicinskie voprosy po umolcaniju lucse ne pokazyvat publicno v profile pitomca

---

## 265 — sviaz s dnevnikom

avtor mozhet soxranit itog temy v lichnyj dnevnik pitomca

naprimer

* vybrannyj metod
* kontakt specialista
* plan dejstvij
* data sledujuscej proverki

publicnye forum nye otvety ne dolzhny avtomaticeski stanovitsia medicinskoj zapisju

---

## 266 — sviaz s medkartoj

xoziajin mozhet vrucnuju soxranit

* ssylku na temu
* rekomendaciju specialista
* rezultat obsuzhdenija
* vopros dlia sledujuscego vizita

---

## 267 — sviaz s zadacami

iz otveta mozhno sozdat zadacu

* zapisatsia v kliniku
* kupit oborudovanie
* izmenit raspisanie
* provesti trening
* proverit dokument
* dobavit napominanie

---

# integracija s gruppami

## 268 — tema gruppy

publikaciju iz gruppy mozhno prevratit v forum nuju temu tolko s ucetom privatnosti

dlia zakrytoj gruppy tema ostajotsia zakrytoj

---

## 269 — obsuzhdenie foruma v gruppe

ssylku na publicnuju temu mozhno otpravit v gruppu

kommentarii gruppy ne dolzhny avtomaticeski kopirovatsia v forum bez soglasija avtorov

---

## 270 — ekspert iz gruppy

moderator gruppy mozhet upomianut proverennogo specialista ili predlozhit temu dlia sessii voprosov

massovye upominanija nuzhno ogranichivat

---

# integracija s kartoj

## 271 — vopros o meste

na stranice parka, kliniki ili salona mozhno zadat strukturirovannyj vopros

naprimer

* rabotaet li voda
* prinimajut li ptic
* est li otdelnaia zona
* mozhno li s krupnoj sobakoj
* est li parkovka

---

## 272 — forum mesta

u mesta mozhet byt otdelnyj razdel voprosov i otvetov

oficialnye predstaviteli mogut otvecat s proverenn oj metkoj

---

## 273 — vremennye dannye

otvet o vremeni raboty ili nalichii uslugi dolzhen imet datu

starye otvety mogut poluchat metku vozmozhno ustarelo

---

# integracija s sobytijami

## 274 — voprosy organizatoru

na stranice sobytija mozhno zadavat

* publicnyj vopros
* privatnyj vopros
* vopros o pitomce
* vopros ob oplate
* vopros o dostupnosti
* vopros o dokumentax

---

## 275 — faq sobytija

poleznye otvety organizator mozhet dobavit v faq

naprimer

* mozhno li bez pitomca
* cto budet pri dozhde
* nuzhna li vakcinacija
* mozhno li s detmi
* mozhno li vernut bilet

---

# integracija s chatom

## 276 — prodolzhit v lichnom dialoge

esli nuzhno obsudit lichnye dannye, avtor mozhet predlozhit perejti v chat

naprimer

* tochnoe mesto
* dokument
* zapis
* kontakt organizatora
* konfidencialnaja informacija

---

## 277 — zascita ot nezhelatelnyx soobsenij

otvet na forume ne dolzhen avtomaticeski davat avtoru pravo pisat v lichnye soobsenija bez zaprosa

---

## 278 — tema iz dlinnogo chata

uchastniki dialoga mogut s soglasiem sozdat obobscennuju forum nuju temu bez lichnyx dannyx

---

# analitika dlia avtorov

## 279 — statistika temy

avtor mozhet videt

* prosmotry
* unikalnye prosmotry
* kolichestvo otvetov
* kolichestvo podpiscikov
* soxranenija
* perehody iz poisk a
* jazyk auditorii
* nalichie poxozhix tem

---

## 280 — kachestvo otvetov

mozhno pokazat

* skolko otvetov ot specialistov
* skolko s istochnikami
* skolko polzovatelej otmetili poleznymi
* skolko otvetov trebuet proverki
* est li prinjatyj otvet

---

## 281 — poiskovye frazy

avtor mozh et videt obobscennye zaprosy, po kotorym naxodili temu

lichnye zaprosy konkretnyx polzovatelej ne dolzhny raskryvatsia

---

## 282 — statistika otveta

avtor otveta mozhet videt

* prosmotry
* poleznye ocenki
* zakladki
* prinjat li otvet
* novye kommentarii
* perehody v profil
* citirovanie v baze znanij

---

# analitika dlia platformy

## 283 — osnovnye pokazateli

platforma mozhet analizirovat

* skolko voprosov poluchaet otvet
* srednee vremia do pervogo otveta
* skolko voprosov resheno
* skolko ostajotsia bez otvetov
* kakie kategorii rastut
* kakix specialistov ne hvataet
* skolko dublikatov
* skolko opasnyx otvetov udaleno

---

## 284 — kachestvo poisk a

mozhno ocenivat

* nashol li polzovatel otvet
* sozdal li novuju temu posle poisk a
* kakie zaprosy bez rezultatov
* kakie sinonimy nuzhno dobavit
* kakie temy slishkom trudno najti

---

## 285 — zdorovje soobscestva

poleznye pokazateli

* dolia novyx avtorov, poluchivshix otvet
* kolichestvo uvazhitelnyx obsuzhdenij
* dolia otvetov s istochnikami
* skorost moderacii
* kolichestvo apellacij
* dolia oshibocnyx nakazanij
* urov en spam a
* vozvrat poleznyx avtorov

---

## 286 — peregruzka ekspertov

platforma dolzhna videt, esli odni i te zhe specialisty poluchajut slishkom mnogo upominanij

mozhno

* ogranichit massovye upominanija
* raspredeliat voprosy
* pokazivat raspisanie
* predlagat uzhe sushestvujuscije otvety
* sozdaivat tematiceskie sessii

---

# texniceskaja logika bez programmnogo koda

## 287 — odna tema kak osnovnoj obekt

u temy dolzhny byt

* unikalnyj identifikator
* avtor
* tip
* zagolovok
* tekst
* kategorija
* metki
* status
* privatnost
* sviazannye pitomcy
* mesto
* media
* vremia
* istorija izmenenij

---

## 288 — otvety kak otdelnye obekty

u otveta dolzhny byt

* avtor
* tema
* tekst
* media
* status
* prinjatie
* golosa
* istochniki
* professionalnaja metka
* istorija redakcii
* moderacija

---

## 289 — kommentarii

kommentarij dolzhen byt priviazan k

* teme
* otvetu
* drugomu kommentariiu v ogranichennoj vlozhennosti
* avtoru
* statusu
* moderacii

---

## 290 — odin golos ot odnogo akkaunta

odin polzovatel ne dolzhen moc postavit mnogo odinakovyx golosov odnomu otvetu

on mozhet izmenit ili ubrat svoj golos

---

## 291 — odno prinjatie otveta

po umolcaniju tema mozhet imet odin osnovnoj prinjatyj otvet

dopolnitelno avtor mozhet otmetit drugie kak tozhe poleznye

---

## 292 — odnovremenn oe redaktirovanie

esli avtor otkryl temu na dvux ustrojstvax, sistema dolzhna predupredit o konflikte i ne poteriat tekst

---

## 293 — stabilnyj poriadok otvetov

pri odnovremennyx publikacijax poriadok dolzhen ostavatsia predskazuemym

varianty sortirovki ne dolzhny slucajno menjat status prinjatogo otveta

---

## 294 — zagruzka porciami

dlinnaja tema ne dolzhna zagruzhat tysjachi otvetov srazu

nuzhno

* podgruzhat po mere prokrutki
* soxranjat poziciju
* perehodit k konkretnomu otvetu
* ne pokazyvat dublikaty
* pravilno obrabatyvat udaleniie

---

## 295 — poiskovyj indeks

posle izmenenija privatnosti nuzhno bystro obnovit

* poisk
* predprosmotry
* rekomendacii
* vneshnju indeksaciju
* kesh
* sviazannye materialy

---

## 296 — privatnyj kontent v kese

zakrytaja ili anonimnaja tema ne dolzhna slucajno raskryvatsia iz starogo kesa

---

## 297 — schetciki

kolichestvo

* otvetov
* kommentarev
* golosov
* podpiscikov
* prosmotrov

dolzhno byt odinakovym na kartocke, v teme i v profile

---

## 298 — udalennye akkaunty

posle udaleniia akkaunta ego vklad mozhet byt

* udalen
* anonimnizirovan
* soxranen kak cast obsuzhdenija
* peredan redakcii pri soglasii

eto zavisit ot tipa dannyx i pravil zascity informacii

---

## 299 — ogranichenie chastoty

dlia borby so spamom mozhno ogranichit

* temy za period
* otvety za minutu
* upominanija
* ssylki
* media
* golosa
* zhaloby bez osnovanija

---

## 300 — dostavka uvedomlenij

odno i to zhe dejstvie ne dolzhno sozdaivat mnogo odinakovyx uvedomlenij pri perezagruzke ili povtornoj otpravke

---

## 301 — obnovlenie prav

posle blokirovki, udaleniia iz gruppy ili izmenenija privatnosti dostup dolzhen ischeznut srazu

---

## 302 — rezervnoe soxranenie cernovika

cernovik dolzhen imet

* poslednjuju versiju
* vremia soxranenija
* ustrojstvo
* konflikt versij
* vosstanovlenie posle oshibki

---

# minimalnaja versija dlia pervogo zapuska

## 303 — osnovnye funkcii

dlia pervoj stabilnoj versii dostatocno realizovat

* kategorii
* podkategorii
* sozdanie voprosa
* sozdanie obsuzhdenija
* zagolovok
* tekst
* fotografii
* video
* sviaz s profilem pitomca
* tematiceskie metki
* otvety
* kommentarii
* reakcii
* poleznye golosa
* prinjatyj otvet
* status resheno
* poisk
* filtry
* poxozhie temy
* proverku dublikatov
* zakladki
* podpiski
* uvedomlenija
* cernoviki
* redaktirovanie
* udaleniie
* zhaloby
* blokirovku
* bazovuju moderaciju
* otvety proverennyx specialistov
* preduprezhdenie v medicinskix temax
* publicnye i zakrytye temy
* mobilnuju versiju
* mnogoiazycnye perevody po zaprosu

---

## 304 — osnovnaja baza znanij dlia pervoj versii

mozhno nacat s

* redakcionnyx statej
* faq
* poshagovyx instrukcij
* prevrashenija reshennoj temy v statju
* istorii versij
* daty poslednej proverki
* istochnikov
* predlozhenij ispravlenij

---

## 305 — cto mozhno dobavit pozhe

* anonimnye temy
* professionalnye zakrytye razdely
* semanticeskij poisk
* sovmestnye otvety
* video otvety
* audio otvety
* avtomaticeskie rasshifrovki
* sessii voprosov ekspertam
* rasshirennuju reputaciju
* redakcionnye komandy
* mestnye versii statej
* oflajn materialy
* sravnenie otvetov
* tematiceskie svodki
* avtomaticeskoe obnaruzhenie ustarevshix dannyx
* slozhnuju anti spam sistemu
* rasshirennuju analitiku
* ai obobscenie dlinn yx tem s pokazom originalov
* avtomaticeskij poisk konfliktujuscix utverzhdenij
* personalnye rekomendacii tem

---

# idealnye scenarii

## 306 — idealnyj scenarij obyc nogo voprosa

xoziajin baksa xochet ponimat, kak priuchit ego spokojno zaxodit v lift

on vybiraet

* kategoriju povedenie
* profil baksa
* vozrast
* metku strax
* tip voprosa lichnyj opyt i sovet specialista

posle zagolovka sistema pokazyvaet tri poxozhie temy

xoziajin citaet ix, no ponimaet, cto ego slucaj otlicaetsia posle konkretnogo gromkogo zvuka

on opisyvaet situaciju, dobavliaet video povedenija i ukazyvaet, cto uze proboval

polzovateli predlagajut neskolko ostorozhnyx metodov, a proverennyj kinolog objasniaet, kogda nuzhna individualnaja konsultacija

cherez nedeliu avtor dobavliaet obnovlenie, vyberaet poleznyj otvet i stavit status resheno

---

## 307 — idealnyj scenarij srocnogo medicinskogo voprosa

xoziajin pishet, cto pitomec tiazhelo dyshit i ne mozhet normalno vstati

do publikacii sistema pokazyvaet zametnoe preduprezhdenie

* ne zhdi otvetov foruma
* pozvoni v kliniku
* najdi blizhaisuju ekstrennuju pomosc
* vozmi s soboj informaciju o vozmozhnom otravlenii ili travme

polzovatel otkryvaet kartu kruglosutocnyx klinik

tema mozhet ostatcia kak vopros, no ne poluchaet prioritet nad realnoj ekstrennoj pomoschiu

moderator zakreplyaet preduprezhdenie i udaljaet opasnye predlozhenija samostojatelnogo lechenija

---

## 308 — idealnyj scenarij dublikata

polzovatel nacinaet sozdavat vopros o dokumentax dlia poezdki s sobakoj iz litvy v polshu

sistema naxodit nedavno obnovlennuju kanoniceskuju temu

v nej est

* kontrolnyj spisok
* ssylki na oficialnye istochniki
* data proverki
* kommentarii po transportu

polzovatel soxraniaet temu i ne sozdaet dublikat

esli u nego est osobaja situacija, naprimer poezdka s pozhilym pitomcem i lekarstvami, on sozdaet novyj vopros so ssylkoj na kanoniceskij material

---

## 309 — idealnyj scenarij ekspert nogo otveta

polzovatel zad ajot vopros o vosstanovlenii posle operacii

proverennyj veterinar otvecaet

* cto mozhno obsudit obobscenno
* cto zavisit ot tipa operacii
* kakie priznaki mogut trebovat srocnogo kontakta s klinikoj
* pochemu nelzia menjat lechenie po forumu
* kak podgotovit voprosy dlia leciascego vracha

riadom pokazyvaetsia ego specializacija i region kvalifikacii

otvet ne soderzhit proizvolnoj dozirovki ili garantii

---

## 310 — idealnyj scenarij mestnoj rekomendacii

xoziajin keshi iscet kliniku v vilniuse, kotoraia prinimaet ptic

forma zaprashivaet

* gorod
* vid pitomca
* nuzhna li srocnaja pomosc
* vremia
* jazyk

forum pokazyvaet poxozhie temy i kartocki klinik

polzovateli delitsia lichnym opytom, a oficialnyj predstavitel kliniki podtverzhdaet, cto priem ptic dostupen tolko po opredelennym dniam

otvet imeet datu, ctob pozhe ego mozhno bylo proverit na aktualnost

---

## 311 — idealnyj scenarij konflikta

v teme o kormlenii nachinaetsia agressivnyj spor

uchastniki perehodiat na lichnye oskorblenija i massovye negativnye golosa

sistema zametaet neobycnuju aktivnost, vrem enno skryvaet schetciki i vkliucaet medlennyj rezhim

moderator

* udaliaet lichnye napadki
* ostavliaet argumenty po teme
* prosit istochniki
* zakreplyaet neytralnoe obobscenie
* preduprezhdaet povtornyx narushitelej

obsuzhdenie prodolzhaetsia tolko po suti voprosa

---

## 312 — idealnyj scenarij prevrashenija v bazu znanij

tema o privykanii koski k perenoske poluchaet mnogo kachestvennyx otvetov

v nej est

* opyt xoziajev
* otvet zoopsixologa
* video
* poshagovyj plan
* obnovlenie avtora
* razbor castyx oshibok

redaktor predlagaet sozdat statju

avtory soglasiaiutsia na ukazanie ix vklada

statja poluchaet

* kratkij vyvod
* poshagovye dejstvija
* cego izbegat
* kogda nuzhen specialist
* istochniki
* datu proverki
* ssylku na originalnoe obsuzhdenie

---

## 313 — idealnyj scenarij anonimnoj podderzhki

polzovatel sozdaet anonimnuju temu o cuvstve viny posle utraty pitomca

publicno ne pokazyvajutsia

* ego imia
* profil
* pitomcy
* gorod
* drugie publikacii

uchastniki ostavljajut podderzhivajuscije otvety

moderator udaliaet neumesnuju reklamu i gruby e kommentarii

polzovatel mozhet zakryt temu, soxranit poleznye otvety ili udalit lichnye detali, ne unichtozhaja vse obsuzhdenie

---

## 314 — idealnyj scenarij mnogoiazycnogo obsuzhdenija

polzovatel sozdaet vopros na litovskom jazyke

drugoj xoziajin citaet ego na russkom cerez avtomaticeskij perevod i otvecaet na russkom

avtor vidit perevedennyj otvet, no mozhet vsegda otkryt original

nazvanie lekarstva, imia pitomca i adres kliniki ostajutsia bez iskazhenija

proverennyj specialist dobavliaet anglijskij otvet s rucnym kratkim perevodom na litovskij

---

## 315 — idealnyj scenarij opasnoj dezinformacii

polzovatel sovetuet dat pitomcu preparat dlia liudej v konkretnoj dozirovke bez dannyx o vese i diagnoze

sistema pomeshaet otvet na proverku i pokazyvaet avtoru voprosa preduprezhdenie ne ispolzovat takoj sovet bez veterinara

medicinskij moderator

* skryvaet opasnuju cast
* ukazyvaet narushennoe pravilo
* preduprezhdaet avtora
* zakreplyaet bezopasnoe primechanie
* pri povtorenii ogranichivaet ego medicinskie otvety

---

## 316 — idealnyj scenarij poiska bez otveta

polzovatel ishchet informaciju o redkom ekzoticeskom zivotnom, no gotovyx tem net

sistema

* pokazyvaet blizkie kategorii
* predlagaet specialistov po ekzoticeskim zivotnym
* predlagaet sozdat novyj vopros
* avtomaticeski dobavliaet pravilnye metki
* rekomenduet ego podxodiascim ekspertam
* dobavliaet v razdel bez otvetov

posle pojavlenija otveta polzovatel i drugie podpisciki polucajut uvedomlenie

sledujuscij punkt — ekspertnoe soobscestvo veterinarov, kinologov, grumerov, felinologov i zoopsixologov
</forum-source-primary>

## Source Part B: Additive Master Extension

<forum-source-extension>
# mandatory additive master extension for the complete animal owner forum and global animal taxonomy

this instruction is an additive extension to every requirement written before this block in the same prompt

all previous requirements, categories, subcategories, database rules, seed rules, translation rules, migration rules, moderation requirements, livewire requirements, tests, documentation requirements, and safety requirements remain mandatory

do not replace, shorten, summarize, simplify, reinterpret, or discard any previous requirement

combine the previous prompt and this extension into one indivisible master specification

quality, completeness, data safety, maintainability, and traceability are more important than implementation speed

do not ask the user follow-up questions

inspect the existing application, make safe documented assumptions, record those assumptions, create the complete plan, and then implement the complete solution

do not stop after planning

do not stop after creating migrations

do not stop after creating seed definitions

do not leave placeholder code, todo comments, empty methods, mock implementations, unconnected user interfaces, untested services, or unfinished documentation

do not claim completion while any atomic requirement remains unimplemented, untested, undocumented, or unaccounted for

## 1. absolute requirement preservation and prompt-loss protection

before changing production code, preserve the complete source specification inside the repository

create or update files equivalent to:

- `docs/requirements/forum-source-prompt.md`
- `docs/requirements/forum-master-requirements.md`
- `docs/requirements/forum-requirements.json`
- `docs/plans/forum-master-plan.md`
- `docs/plans/forum-current-progress.md`
- `docs/traceability/forum-requirements-matrix.md`
- `docs/audits/forum-existing-system-audit.md`
- `docs/audits/forum-gap-analysis.md`
- `docs/audits/forum-final-completeness-audit.md`
- `docs/decisions/forum-architecture-decisions.md`
- `docs/decisions/forum-assumptions.md`
- `docs/decisions/forum-conflicts.md`

use the existing project documentation structure when equivalent files already exist

do not create duplicate documentation systems

copy the complete combined prompt into the source prompt document without removing any requirement

record a checksum or another deterministic fingerprint of the preserved source prompt

the source prompt document is immutable implementation input

future changes to requirements must be appended as dated revisions instead of silently replacing earlier instructions

## 2. mandatory atomic requirement extraction

before implementation, decompose the complete prompt into atomic requirements

an atomic requirement is the smallest independently verifiable instruction

do not place several unrelated requirements under one vague plan item

assign every atomic requirement a permanent identifier

use identifiers similar to:

- `forum.plan.001`
- `forum.category.health.001`
- `forum.category.before-ownership.001`
- `forum.feature.karma.001`
- `forum.feature.consensus.001`
- `forum.feature.reports.001`
- `forum.feature.search.001`
- `forum.moderation.001`
- `forum.security.001`
- `forum.translation.001`
- `forum.seed.001`
- `forum.test.001`
- `animal.taxonomy.001`
- `animal.import.001`
- `animal.translation.001`
- `animal.test.001`

every sentence, numbered item, bullet, sub-bullet, category, subcategory, field, state, permission, rule, workflow, test requirement, documentation requirement, and final report requirement must be represented by one or more atomic requirement identifiers

never use a single requirement such as `implement all forum functions`

never mark multiple hundred requirements as complete through one generic checkbox

the requirements matrix must contain at least:

- requirement id
- verbatim source requirement
- normalized implementation requirement
- source section
- domain
- priority
- dependencies
- current implementation status
- discovered existing files
- planned files
- database impact
- backend impact
- livewire impact
- interface impact
- authorization impact
- privacy impact
- security impact
- moderation impact
- translation impact
- cache impact
- migration and backfill impact
- seed impact
- factory impact
- test identifiers
- documentation identifiers
- implementation status
- verification status
- evidence
- unresolved risks
- final result

allowed requirement states:

- discovered
- analyzed
- planned
- approved-by-specification
- in-progress
- implemented
- migrated
- translated
- tested
- documented
- verified
- blocked
- intentionally-not-applicable

`intentionally-not-applicable` requires a detailed technical explanation and evidence

`blocked` is not completion

no requirement may be marked `verified` without file-level or test-level evidence

## 3. mandatory plan-first contract for every phase and every future prompt

every prompt, sub-prompt, phase, task file, work package, continuation task, and generated implementation instruction must begin with its own plan

this applies even when the task looks small

each phase plan must include:

- exact requirement ids included in the phase
- current implementation analysis
- desired result
- affected files
- files expected to be created
- files expected to be modified
- schema changes
- data migration strategy
- rollback strategy
- legacy compatibility strategy
- authorization changes
- validation changes
- translation changes
- interface changes
- accessibility changes
- cache changes
- security risks
- privacy risks
- abuse risks
- tests to create
- tests to update
- documentation to update
- acceptance criteria
- verification procedure
- completion evidence

before implementation of a phase:

1. read the source prompt
2. read the master requirements
3. read the requirements matrix
4. read the current progress document
5. inspect the current repository state
6. inspect all uncommitted changes
7. update the phase plan
8. confirm that every selected requirement id appears in the phase plan
9. only then begin implementation

after implementation of a phase:

1. update every affected requirement status
2. link changed files
3. link tests
4. link documentation
5. record migration and compatibility results
6. record discovered additional work
7. add new requirements instead of silently ignoring discovered gaps
8. run a phase completeness audit
9. update the current progress document
10. prepare the next phase plan

do not rely on temporary conversation memory

all state required to continue work must exist in repository documentation

if the work continues in a new codex context, first read the source prompt, requirements matrix, plans, decisions, and progress files

## 4. completeness gates

the implementation must pass these gates

### gate 0: source preservation

- the complete prompt is preserved
- every source section is identifiable
- no source text is lost

### gate 1: discovery

- existing architecture is documented
- current forum data model is documented
- current user and pet model is documented
- current translation system is documented
- current permissions are documented
- current moderation system is documented
- current search and cache system is documented
- current seeds, factories, and tests are documented
- current deployment constraints are documented

### gate 2: atomic plan coverage

- every requirement has an id
- every requirement has a plan item
- every plan item has acceptance criteria
- every plan item has expected evidence
- no unplanned production change is allowed

### gate 3: implementation

- schema is complete
- services and actions are complete
- policies are complete
- validation is complete
- livewire components are complete
- interfaces are complete
- translations are complete
- migration and backfill are complete
- seeds are complete

### gate 4: tests

- every critical business rule has tests
- every permission boundary has tests
- every destructive-risk path has tests
- every idempotent seed has rerun tests
- every reputation and voting race condition has tests
- every moderation state transition has tests
- every taxonomy import state has tests

### gate 5: documentation

- architecture is documented
- administration is documented
- moderation is documented
- taxonomy is documented
- seed behavior is documented
- migration behavior is documented
- recovery procedures are documented

### gate 6: final traceability

- every requirement ends as verified or explicitly blocked
- every verified requirement includes evidence
- no vague completion statements are accepted
- the final audit reports missing coverage as an error

## 5. mandatory technical baseline

use:

- laravel 13
- php 8.5
- livewire 4 using the latest compatible release supported by the repository
- tailwind css 4
- flux or flux pro when already installed and appropriate
- eloquent
- policies
- gates where appropriate
- dedicated action classes
- dedicated query classes
- domain services
- validated livewire form objects or form request classes where appropriate
- php enums for stable states
- value objects for complex domain values
- database transactions
- database constraints
- factories
- production-safe seeders
- development-only demo seeders
- automated php tests
- the existing project translation system
- the existing cache infrastructure
- the existing search infrastructure where suitable

do not use:

- laravel volt
- `@php` inside blade templates
- direct database queries from blade templates
- direct service container resolution from blade templates
- authorization based only on hidden buttons
- translated names as database identifiers
- category display names as relationship keys
- destructive truncation in production-safe seeders
- uncontrolled mass assignment
- unsafe html rendering
- unvalidated uploads
- unstable external ids as primary database keys
- raw sql without a documented need
- duplicated business logic
- duplicated translation systems
- unnecessary javascript frameworks
- unbounded queries
- n+1 queries
- automatic permanent bans without human-review safeguards
- automatic professional verification
- popularity as proof of medical or legal correctness
- purchasable karma
- paid trust levels
- pay-to-win moderation influence

inspect the project before introducing queues, schedulers, cron, supervisor, websocket servers, external search servers, or other operational dependencies

do not make critical forum functionality depend on new infrastructure that the existing deployment cannot support

where a long process is necessary, provide a safe resumable admin-triggered or command-triggered mechanism that follows current project constraints

work only in the current main branch when git is available

do not create another branch

do not overwrite unrelated uncommitted work

## 6. preserve the original twenty top-level forum categories

all twenty top-level categories and every subcategory from the previous prompt remain mandatory

the previous categories are:

1. health
2. nutrition
3. behavior
4. training and education
5. everyday care
6. walks, exercise, and places
7. travel and documents
8. adoption, rescue, and shelters
9. lost and found
10. breeding, genetics, and newborn care
11. species and breed communities
12. services and professionals
13. gear, products, and technology
14. marketplace and exchanges
15. community, stories, and daily life
16. owner support and wellbeing
17. laws, rights, and animal welfare
18. emergencies and safety alerts
19. events, clubs, and activities
20. platform help and support

do not remove, flatten, merge, or shorten their previously specified subcategories

the following categories are additional first-level categories

## 7. additional first-level category 21: before getting an animal

stable key:

- `forum.before-ownership`

purpose:

help people make a responsible decision before adopting, purchasing, rescuing, fostering, or otherwise accepting responsibility for an animal

subcategories:

1. am i ready for an animal
2. choosing an animal for my lifestyle
3. choosing between animal species
4. choosing a dog
5. choosing a cat
6. choosing a bird
7. choosing a rabbit
8. choosing a rodent
9. choosing a reptile
10. choosing an amphibian
11. choosing aquarium animals
12. choosing a horse
13. choosing farm animals
14. choosing an exotic animal
15. choosing an invertebrate
16. first animal for a beginner
17. animals suitable for experienced owners
18. household agreement before getting an animal
19. children and the decision to get an animal
20. existing animals in the household
21. allergies and medical considerations
22. housing restrictions
23. landlord permission
24. local legal restrictions
25. expected lifespan
26. daily time requirements
27. exercise requirements
28. social and emotional requirements
29. training requirements
30. grooming requirements
31. habitat requirements
32. expected veterinary costs
33. food and supply costs
34. insurance planning
35. emergency financial planning
36. holiday and travel planning
37. temporary caregiver planning
38. long-term contingency planning
39. adoption versus responsible breeder
40. evaluating a shelter
41. evaluating a rescue organization
42. evaluating a breeder
43. avoiding irresponsible sellers
44. avoiding adoption and sales scams
45. ethical sourcing
46. meeting an animal before commitment
47. preparing the home
48. essential starter supplies
49. safe transport home
50. the first twenty-four hours
51. the first week
52. the first month
53. first veterinary appointment
54. first introductions to people
55. first introductions to other animals
56. first-time owner mistakes
57. returning or rehoming responsibly
58. owner readiness checklists
59. total cost of ownership calculators
60. species and lifestyle comparison tools

## 8. additional first-level category 22: special needs, disability, and accessibility

stable key:

- `forum.special-needs-accessibility`

purpose:

support animals with disabilities, chronic limitations, special care requirements, and owners who need accessible care solutions

subcategories:

1. general special-needs care
2. mobility limitations
3. paralysis
4. amputee animals
5. wheelchair users
6. blind animals
7. partially sighted animals
8. deaf animals
9. neurological disabilities
10. developmental disabilities
11. vestibular conditions
12. incontinence management
13. chronic pain support
14. seizure-related care
15. feeding disabilities
16. swallowing difficulties
17. breathing-related limitations
18. skin and wound management
19. long-term medication routines
20. pressure sore prevention
21. rehabilitation
22. physiotherapy
23. hydrotherapy
24. massage and safe physical support
25. mobility aids
26. wheelchairs
27. prosthetics
28. braces and supports
29. lifting harnesses
30. ramps and stairs
31. accessible beds
32. accessible litter and toilet areas
33. home adaptations
34. garden adaptations
35. vehicle adaptations
36. accessible travel
37. accessible public places
38. enrichment for limited-mobility animals
39. training blind animals
40. training deaf animals
41. communication systems
42. quality-of-life assessment
43. special-needs adoption
44. special-needs fostering
45. financial support
46. caregiver fatigue
47. caregiver routines
48. disabled owners caring for animals
49. assistance for elderly owners
50. success stories
51. memorial and end-of-life support
52. verified accessibility product reviews
53. accessibility service directories
54. specialist professional directories

## 9. additional first-level category 23: wildlife and human-animal coexistence

stable key:

- `forum.wildlife-coexistence`

purpose:

help users interact safely, legally, and ethically with wild animals without treating all wildlife as pets

subcategories:

1. general wildlife questions
2. when to help a wild animal
3. when not to intervene
4. injured wildlife
5. orphaned wildlife
6. apparently abandoned young animals
7. licensed wildlife rehabilitators
8. wildlife rescue organizations
9. emergency wildlife transport
10. urban wildlife
11. rural wildlife
12. garden wildlife
13. wild birds
14. birds of prey
15. bats
16. hedgehogs and similar small mammals
17. foxes and similar urban predators
18. deer and large mammals
19. marine mammals
20. sea turtles
21. wild reptiles
22. wild amphibians
23. wild fish
24. insects and pollinators
25. spiders and other arachnids
26. injured animals near roads
27. window collisions
28. fishing line and net entanglement
29. plastic and waste injuries
30. wildlife in buildings
31. wildlife in gardens
32. coexistence with predators
33. protecting poultry and livestock
34. humane conflict prevention
35. wildlife feeding discussions
36. bird feeders and hygiene
37. wildlife water sources
38. wildlife-friendly gardens
39. nesting boxes
40. pollinator habitats
41. invasive species
42. native species
43. protected species
44. wildlife photography ethics
45. wildlife tourism ethics
46. illegal wildlife trade
47. suspected poaching
48. reporting wildlife crime
49. roadkill reporting
50. wildlife observation records
51. release after rehabilitation
52. wildlife disease alerts
53. country-specific wildlife laws
54. coexistence success stories
55. citizen observation projects

## 10. additional first-level category 24: one health, zoonoses, and human safety

stable key:

- `forum.one-health-human-safety`

purpose:

cover the intersection between animal health, human health, household safety, and public health without replacing medical or veterinary professionals

subcategories:

1. general one-health discussions
2. zoonotic diseases
3. reverse zoonoses
4. household hygiene
5. handwashing and sanitation
6. bites
7. scratches
8. allergy management
9. asthma and animals
10. pregnancy and animal contact
11. newborn children and animals
12. immunocompromised people
13. elderly family members
14. young children
15. raw food hygiene
16. food-borne risks
17. parasite transmission
18. flea and tick risks to people
19. ringworm and fungal risks
20. household quarantine
21. introducing a recently rescued animal
22. cleaning after infectious illness
23. safe waste disposal
24. litter box hygiene
25. aquarium hygiene
26. reptile and amphibian hygiene
27. farm animal contact
28. wildlife contact
29. occupational animal exposure
30. groomer safety
31. shelter worker safety
32. veterinary worker safety
33. trainer and walker safety
34. public-health alerts
35. outbreak discussions
36. coordinating with veterinarians and doctors
37. mental health benefits of animals
38. emotional stress related to animal care
39. misinformation and stigma
40. community health education
41. safe school and educational visits
42. country-specific public-health guidance

display a clear notice that discussions do not replace a physician, veterinarian, public-health authority, or emergency service

## 11. additional first-level category 25: animal science, research, and evidence

stable key:

- `forum.animal-science-evidence`

purpose:

create an evidence-oriented space for understanding animal science, evaluating claims, and discussing research responsibly

subcategories:

1. general animal science
2. anatomy
3. physiology
4. genetics
5. epigenetics
6. evolution
7. domestication
8. cognition
9. emotions
10. learning science
11. behavior research
12. welfare science
13. veterinary research
14. nutrition research
15. reproduction research
16. aging research
17. pain research
18. rehabilitation research
19. environmental enrichment research
20. comparative medicine
21. conservation science
22. taxonomy and systematics
23. ecology
24. population science
25. epidemiology
26. research methods
27. study design
28. statistics
29. interpreting risk
30. correlation and causation
31. systematic reviews
32. meta-analyses
33. clinical trials
34. observational studies
35. case reports
36. laboratory studies
37. preprints
38. peer review
39. replication
40. conflicts of interest
41. funding disclosures
42. product claims
43. advertising claim analysis
44. source verification
45. evidence grading
46. myth checking
47. outdated recommendations
48. research summaries
49. plain-language science explanations
50. research requests
51. citizen science
52. ethical research discussions
53. open data
54. research corrections and retractions

## 12. additional first-level category 26: working, service, therapy, and assistance animals

stable key:

- `forum.working-service-assistance`

purpose:

cover training, care, welfare, legal access, retirement, and handler support for animals performing structured work

subcategories:

1. general working-animal discussions
2. guide dogs
3. hearing assistance dogs
4. mobility assistance animals
5. medical alert animals
6. seizure alert discussions
7. diabetes alert discussions
8. psychiatric assistance animals
9. autism assistance animals
10. therapy animals
11. emotional support animal discussions
12. legal differences between assistance and support animals
13. public-access training
14. handler responsibilities
15. access disputes
16. workplace access
17. school and university access
18. transport access
19. travel with assistance animals
20. search and rescue animals
21. tracking animals
22. detection animals
23. police animals
24. military working animals
25. security animals
26. livestock guardian animals
27. herding animals
28. hunting and field-working animals
29. sled animals
30. messenger and historical working animals
31. working horses
32. working donkeys and mules
33. animal-assisted interventions
34. therapy program standards
35. choosing a candidate animal
36. health screening
37. temperament screening
38. training organizations
39. trainer verification
40. owner-trained assistance animals
41. equipment
42. welfare during work
43. rest and workload
44. injury prevention
45. retirement
46. rehoming retired working animals
47. handler wellbeing
48. fraudulent representation
49. certification and documentation
50. country-specific regulations
51. working-animal success stories

## 13. additional first-level category 27: professional community and continuing education

stable key:

- `forum.professional-community`

purpose:

provide a verified and appropriately separated space for animal-care professionals, students, organizations, and interdisciplinary cooperation

subcategories:

1. veterinary professionals
2. veterinary students
3. veterinary nurses and technicians
4. veterinary assistants
5. veterinary specialists
6. veterinary behaviorists
7. animal trainers
8. behavior consultants
9. groomers
10. pet sitters
11. dog walkers
12. boarding staff
13. shelter staff
14. rescue coordinators
15. foster coordinators
16. wildlife rehabilitators
17. animal nutrition professionals
18. physiotherapists
19. rehabilitation professionals
20. farriers
21. equine professionals
22. breeders and breeding ethics
23. animal welfare inspectors
24. researchers
25. laboratory animal welfare
26. animal-law professionals
27. insurance professionals
28. pet transport professionals
29. pet-product professionals
30. continuing education
31. certification programs
32. professional standards
33. professional ethics
34. anonymized case discussions
35. interdisciplinary referrals
36. mentoring
37. internships
38. apprenticeships
39. conferences
40. webinars
41. professional literature
42. career development
43. employment discussions
44. workplace safety
45. professional burnout
46. compassion fatigue
47. verified professional questions
48. public expert question sessions
49. professional collaboration requests
50. country-specific professional regulations

sensitive case discussions must prohibit identifiable client, patient, and private information

## 14. additional first-level category 28: volunteering, rescue operations, and mutual aid

stable key:

- `forum.volunteering-rescue-operations`

purpose:

support organized practical work for shelters, rescues, foster networks, search teams, and owners in temporary crisis

subcategories:

1. becoming a volunteer
2. volunteer onboarding
3. volunteer verification
4. volunteer safety
5. shelter volunteering
6. rescue volunteering
7. wildlife volunteering
8. foster volunteering
9. transport volunteers
10. search volunteers
11. event volunteers
12. remote volunteering
13. translation volunteering
14. photography volunteering
15. fundraising volunteering
16. foster networks
17. emergency foster placement
18. temporary boarding
19. transport chains
20. local transport
21. international transport
22. intake coordination
23. medical triage coordination
24. quarantine coordination
25. supply requests
26. food banks
27. medicine support requests
28. equipment lending
29. donation coordination
30. donor transparency
31. fundraising transparency
32. verified fundraising
33. emergency case coordination
34. disaster response
35. evacuation support
36. lost-animal search teams
37. trap-neuter-return operations
38. street-animal feeding coordination
39. sanctuary support
40. volunteer scheduling
41. shift handovers
42. safety checklists
43. incident reporting
44. volunteer disputes
45. volunteer recognition
46. volunteer burnout
47. rescue case management
48. operation reports
49. completed rescue stories
50. local mutual-aid groups

## 15. additional first-level category 29: ethics, welfare, and sustainability

stable key:

- `forum.ethics-welfare-sustainability`

purpose:

provide structured, respectful discussions about animal welfare, difficult ethical choices, and environmental responsibility

subcategories:

1. animal welfare principles
2. quality of life
3. welfare assessment
4. responsible ownership
5. ethical adoption
6. ethical breeding
7. ethical rehoming
8. pet overpopulation
9. sterilization ethics
10. elective procedures
11. cosmetic procedures
12. declawing discussions
13. tail and ear procedure discussions
14. exotic animal ownership ethics
15. wildlife captivity
16. animal entertainment
17. animal tourism
18. animal photography ethics
19. working-animal ethics
20. competition-animal welfare
21. animal clothing and accessories
22. euthanasia ethics
23. hospice care ethics
24. keeping animals with severe illness
25. behavioral euthanasia discussions
26. shelter population decisions
27. sanctuary ethics
28. rescue ethics
29. feeding wildlife ethics
30. invasive species management
31. laboratory animal ethics
32. farming welfare
33. transport welfare
34. product testing discussions
35. sustainable animal food
36. sustainable litter
37. sustainable packaging
38. responsible consumption
39. second-hand equipment
40. repair and reuse
41. environmental impact of ownership
42. biodiversity protection
43. responsible outdoor access
44. wildlife disturbance
45. ethical disagreement and debate
46. welfare campaigns
47. policy proposals
48. evidence-based ethical discussions

## 16. additional first-level category 30: home, housing, garden, and safe environment

stable key:

- `forum.home-housing-environment`

purpose:

help users create practical, safe, accessible, and species-appropriate living environments

subcategories:

1. apartment living
2. house living
3. rental housing
4. landlord communication
5. pet clauses and agreements
6. shared housing
7. dormitories and student housing
8. balconies
9. windows
10. doors and escape prevention
11. flooring
12. furniture protection
13. safe sleeping areas
14. feeding areas
15. water areas
16. toilet and litter areas
17. quarantine rooms
18. multi-pet zoning
19. separating incompatible animals
20. indoor fencing
21. outdoor fencing
22. garden safety
23. digging prevention
24. toxic plants
25. fertilizers
26. pesticides
27. rodent-control products
28. household cleaning products
29. medication storage
30. food storage
31. electrical safety
32. fire safety
33. heating safety
34. cooling and ventilation
35. humidity control
36. air quality
37. smoke and fragrance exposure
38. noise reduction
39. soundproofing
40. lighting
41. aquarium placement
42. terrarium placement
43. cage and aviary placement
44. stable and coop design
45. accessibility adaptations
46. senior-animal adaptations
47. special-needs adaptations
48. security cameras
49. privacy concerns
50. emergency exits
51. evacuation plans
52. moving home
53. renovation with animals
54. temporary construction safety
55. home inspections and checklists

## 17. additional first-level category 31: aquatic life, aquariums, and ponds

stable key:

- `forum.aquatic-life`

purpose:

provide a complete community for freshwater, marine, brackish, pond, fish, coral, and aquatic invertebrate care

subcategories:

1. aquarium basics
2. freshwater aquariums
3. marine aquariums
4. brackish aquariums
5. cold-water aquariums
6. tropical aquariums
7. planted aquariums
8. reef aquariums
9. fish-only marine systems
10. nano aquariums
11. large aquariums
12. species-only aquariums
13. biotope aquariums
14. outdoor ponds
15. indoor ponds
16. aquarium cycling
17. beneficial bacteria
18. water chemistry
19. ph
20. hardness
21. ammonia
22. nitrite
23. nitrate
24. salinity
25. oxygenation
26. filtration
27. mechanical filtration
28. biological filtration
29. chemical filtration
30. heating
31. cooling
32. lighting
33. carbon dioxide systems
34. aquarium plants
35. algae control
36. stocking plans
37. compatibility
38. schooling and social needs
39. quarantine
40. acclimation
41. feeding
42. disease
43. medication safety
44. breeding fish
45. raising fry
46. shrimp
47. crayfish
48. crabs
49. snails
50. corals
51. anemones
52. jellyfish
53. other aquatic invertebrates
54. aquascaping
55. maintenance routines
56. water changes
57. emergency power loss
58. leaks and equipment failures
59. fish transport
60. ethical sourcing
61. wild-caught versus captive-bred
62. invasive release prevention
63. aquarium rescue and rehoming
64. aquarium photography
65. aquarium journals

## 18. additional first-level category 32: terrariums, amphibians, and invertebrates

stable key:

- `forum.terrariums-invertebrates`

purpose:

support responsible care of reptiles, amphibians, arachnids, insects, terrestrial crustaceans, molluscs, and other terrarium animals

subcategories:

1. terrarium basics
2. bioactive terrariums
3. desert terrariums
4. tropical terrariums
5. arboreal terrariums
6. aquatic terrariums
7. paludariums
8. snakes
9. lizards
10. geckos
11. monitors
12. iguanas
13. chameleons
14. turtles
15. tortoises
16. legally kept crocodilians
17. frogs
18. toads
19. salamanders
20. newts
21. caecilians
22. spiders
23. tarantulas
24. scorpions
25. whip scorpions
26. harvestmen
27. mites and specialist cultures
28. mantises
29. beetles
30. stick insects
31. leaf insects
32. butterflies and moths
33. ants
34. bees and social insects
35. millipedes
36. centipedes
37. isopods
38. springtails
39. land snails
40. land slugs
41. hermit crabs
42. other terrestrial crustaceans
43. enclosure dimensions
44. escape prevention
45. ventilation
46. temperature gradients
47. humidity
48. ultraviolet lighting
49. heating equipment
50. substrates
51. hides and climbing structures
52. water features
53. live plants
54. live food
55. feeder insect colonies
56. nutrition and supplements
57. moulting
58. hibernation and brumation
59. quarantine
60. disease
61. breeding
62. eggs and incubation
63. venomous animal safety
64. legal restrictions
65. specialist veterinarians
66. ethical sourcing
67. conservation-sensitive species
68. rehoming
69. terrarium journals

## 19. additional first-level category 33: birds and aviculture

stable key:

- `forum.birds-aviculture`

purpose:

provide a dedicated area for companion birds, aviary birds, poultry-related companion care, rescue birds, and responsible aviculture

subcategories:

1. bird ownership basics
2. parrots
3. parakeets
4. budgerigars
5. cockatiels
6. cockatoos
7. macaws
8. african grey parrots
9. amazon parrots
10. lovebirds
11. lorikeets
12. finches
13. canaries
14. pigeons
15. doves
16. softbills
17. poultry as companion animals
18. ducks and geese as companion animals
19. quail
20. legally kept raptors
21. rescue birds
22. wild-born bird concerns
23. cages
24. aviaries
25. free-flight rooms
26. safe indoor flight
27. outdoor flight
28. wing clipping discussions
29. escape prevention
30. household hazards
31. air quality
32. non-stick coating risks
33. nutrition
34. seed diets
35. pellet diets
36. fresh foods
37. foraging
38. enrichment
39. social needs
40. flock dynamics
41. human bonding
42. training
43. recall
44. handling
45. biting
46. screaming and vocalization
47. feather plucking
48. hormonal behavior
49. bathing
50. beak care
51. nail care
52. feather care
53. health and disease
54. quarantine
55. breeding
56. eggs
57. chicks
58. hand feeding
59. travel
60. lost birds
61. identification rings
62. microchips and identification
63. legal documentation
64. specialist veterinarians
65. bird rescue and adoption
66. aviculture ethics
67. bird journals

## 20. additional first-level category 34: horses and equestrian life

stable key:

- `forum.horses-equestrian`

purpose:

provide an extensive community for horses, ponies, donkeys, mules, equine care, riding, training, work, rescue, and ownership

subcategories:

1. first-time horse owners
2. horses
3. ponies
4. donkeys
5. mules
6. miniature equines
7. choosing an equine
8. purchasing
9. adoption and rescue
10. boarding
11. stable management
12. pasture management
13. fencing
14. shelter
15. bedding
16. feeding
17. forage
18. supplements
19. weight management
20. water
21. hoof care
22. farriers
23. dental care
24. veterinary care
25. vaccinations
26. parasite control
27. colic discussions
28. lameness
29. injuries
30. rehabilitation
31. senior equines
32. special-needs equines
33. groundwork
34. handling
35. behavior
36. training
37. riding basics
38. trail riding
39. dressage
40. jumping
41. endurance
42. driving
43. western disciplines
44. equine sports
45. tack
46. saddle fitting
47. transport
48. trailers
49. breeding
50. pregnancy
51. foaling
52. raising young equines
53. working equines
54. therapy programs
55. equine welfare
56. rescue operations
57. ownership costs
58. contracts and disputes
59. insurance
60. equestrian events
61. equine journals

## 21. additional first-level category 35: farm, homestead, and smallholding animals

stable key:

- `forum.farm-homestead`

purpose:

cover small farms, household livestock, sanctuaries, companion farm animals, and responsible smallholding management

subcategories:

1. smallholding basics
2. cattle
3. dairy cattle
4. beef cattle
5. pigs
6. sheep
7. goats
8. alpacas
9. llamas
10. farm rabbits
11. chickens
12. ducks
13. geese
14. turkeys
15. quail
16. guinea fowl
17. pigeons
18. farm dogs
19. barn cats
20. farm horses
21. donkeys and mules
22. bees
23. other managed invertebrates
24. choosing species
25. mixed-species holdings
26. housing
27. barns
28. coops
29. fencing
30. pasture
31. predator protection
32. feeding
33. water systems
34. health
35. vaccinations
36. parasite control
37. biosecurity
38. quarantine
39. breeding
40. pregnancy and birth
41. newborn care
42. hoof and foot care
43. humane handling
44. transport
45. animal identification
46. records
47. permits
48. neighbour relations
49. manure management
50. environmental protection
51. food-product hygiene
52. companion versus production discussions
53. farm-animal rescue
54. sanctuaries
55. retirement care
56. welfare standards
57. disaster planning
58. smallholding journals

## 22. additional first-level category 36: rare, exotic, and unusual animals

stable key:

- `forum.rare-exotic-unusual`

purpose:

provide welfare-oriented information about unusual animals while clearly enforcing legal, ethical, safety, and specialist-care requirements

subcategories:

1. exotic animal suitability
2. legal ownership checks
3. permits
4. ethical sourcing
5. captive-bred versus wild-caught
6. specialist veterinarians
7. emergency planning
8. escape prevention
9. public safety
10. exotic mammals
11. sugar gliders
12. hedgehogs kept legally
13. unusual rodents
14. ferrets
15. skunks kept legally
16. raccoon-like animals kept legally
17. fox-like animals kept legally
18. genets and related species
19. capybaras
20. unusual hoofed animals
21. unusual marsupials
22. primate ownership welfare discussions
23. unusual birds
24. unusual reptiles
25. unusual amphibians
26. venomous species
27. unusual fish
28. cephalopods
29. jellyfish
30. corals
31. unusual crustaceans
32. unusual molluscs
33. unusual arachnids
34. unusual insects
35. colony organisms
36. species identification
37. habitat design
38. climate control
39. specialist diets
40. live-food systems
41. enrichment
42. handling limitations
43. disease
44. quarantine
45. breeding restrictions
46. rehoming
47. rescue
48. conservation concerns
49. illegal wildlife trade prevention
50. unsuitable-animal education

the existence of a subcategory must never imply that keeping a particular animal is legal, ethical, safe, or recommended

## 23. additional first-level category 37: identification, taxonomy, and species discovery

stable key:

- `forum.identification-taxonomy`

purpose:

help users identify animals and understand scientific classification without presenting uncertain crowd guesses as verified facts

subcategories:

1. general identification requests
2. dog breed identification
3. cat breed identification
4. domestic-animal identification
5. bird identification
6. fish identification
7. reptile identification
8. amphibian identification
9. insect identification
10. spider identification
11. arachnid identification
12. crustacean identification
13. mollusc identification
14. marine invertebrate identification
15. freshwater invertebrate identification
16. terrestrial invertebrate identification
17. wildlife identification
18. tracks
19. footprints
20. droppings and signs
21. feathers
22. fur and hair
23. shells
24. nests
25. eggs
26. larvae
27. pupae
28. animal sounds
29. underwater observations
30. microscope observations
31. photograph requirements
32. location and habitat context
33. seasonal context
34. look-alike species
35. dangerous look-alikes
36. native species
37. introduced species
38. invasive species
39. protected species
40. scientific names
41. common names
42. synonyms
43. taxonomic ranks
44. taxonomic changes
45. uncertain identification
46. expert-reviewed identification
47. community consensus identification
48. dna-based identification discussions
49. ethical observation
50. citizen-science records
51. correction requests
52. identification success stories

## 24. additional first-level category 38: diy, crafts, repairs, and creative projects

stable key:

- `forum.diy-creative`

purpose:

support safe owner-made products, repairs, enrichment, creative work, and accessibility projects

subcategories:

1. diy safety
2. material safety
3. tool safety
4. enrichment toys
5. puzzle feeders
6. chew items
7. scratching furniture
8. beds
9. blankets
10. crates
11. carriers
12. indoor pens
13. outdoor enclosures
14. cages
15. aviaries
16. aquarium stands
17. aquarium decorations
18. terrariums
19. terrarium decorations
20. ponds
21. ramps
22. stairs
23. mobility aids
24. lifting aids
25. feeding stations
26. water stations
27. litter furniture
28. storage systems
29. collars
30. harnesses
31. clothing
32. protective footwear
33. identification tags
34. sewing
35. knitting
36. crochet
37. woodworking
38. metalwork
39. safe three-dimensional printing
40. safe electronics
41. sensor projects
42. camera setups
43. photography backgrounds
44. memorial projects
45. portraits and art
46. repair guides
47. restoration
48. upcycling
49. project plans
50. cost breakdowns
51. before-and-after projects
52. peer safety reviews
53. failed projects and lessons

dangerous projects must be restricted, warned, reviewed, or prohibited according to risk

## 25. additional first-level category 39: technology, data, automation, and citizen science

stable key:

- `forum.animal-technology-data`

purpose:

cover animal-related technology, privacy, data ownership, monitoring, automation, and responsible citizen-science tools

subcategories:

1. animal technology basics
2. gps trackers
3. smart collars
4. activity trackers
5. health trackers
6. smart feeders
7. water fountains
8. pet cameras
9. automatic doors
10. microchips
11. microchip scanners
12. temperature sensors
13. humidity sensors
14. air-quality sensors
15. aquarium controllers
16. terrarium controllers
17. stable monitoring
18. coop monitoring
19. automatic lighting
20. automatic heating
21. emergency power systems
22. mobile applications
23. web applications
24. data synchronization
25. integrations
26. application programming interfaces
27. data export
28. backups
29. privacy
30. location-data safety
31. camera privacy
32. account security
33. device security
34. product vulnerabilities
35. open-source tools
36. data visualization
37. health journals
38. behavior journals
39. citizen-science platforms
40. biodiversity observations
41. mapping
42. species identification technology
43. artificial-intelligence suggestions
44. artificial-intelligence limitations
45. false identification risks
46. do-it-yourself electronics
47. accessibility technology
48. product comparisons
49. long-term reliability
50. repairability
51. electronic waste
52. responsible data sharing

artificial-intelligence output must never be displayed as a verified veterinary diagnosis, legal conclusion, or definitive species identification without appropriate review

## 26. additional first-level category 40: animal organizations and business operations

stable key:

- `forum.animal-organizations-business`

purpose:

support responsible operations for clinics, shelters, rescues, service providers, nonprofit organizations, and animal-related businesses

subcategories:

1. starting an animal-related organization
2. business planning
3. nonprofit planning
4. legal structure
5. permits and licenses
6. insurance
7. clinic operations
8. shelter operations
9. rescue operations
10. sanctuary operations
11. grooming operations
12. training operations
13. boarding operations
14. daycare operations
15. pet-sitting operations
16. walking operations
17. transport operations
18. breeding-business compliance
19. pet-store operations
20. product manufacturing
21. food manufacturing
22. photography businesses
23. insurance organizations
24. staff roles
25. recruitment
26. employee training
27. volunteer management
28. schedules
29. bookings
30. client records
31. animal records
32. consent forms
33. standard operating procedures
34. incident management
35. complaints
36. dispute resolution
37. data protection
38. cybersecurity
39. inventory
40. supplier management
41. facility safety
42. infection control
43. emergency planning
44. financial controls
45. donor management
46. fundraising governance
47. transparency
48. service quality
49. accreditation
50. partnerships
51. marketing ethics
52. sponsored content
53. crisis communication
54. succession planning
55. organization closure
56. professional collaboration

## 27. additional first-level category 41: knowledge library, guides, and reference materials

stable key:

- `forum.knowledge-library`

purpose:

turn verified community knowledge into versioned, searchable, translated, and maintainable reference content

subcategories:

1. beginner guides
2. owner checklists
3. species care guides
4. breed guides
5. life-stage guides
6. health preparation guides
7. emergency guides
8. nutrition explainers
9. behavior explainers
10. training plans
11. grooming guides
12. housing guides
13. travel guides
14. document guides
15. adoption guides
16. fostering guides
17. lost-animal guides
18. product-selection guides
19. service-selection guides
20. legal-information guides
21. welfare guides
22. accessibility guides
23. seasonal guides
24. printable guides
25. downloadable checklists
26. frequently asked questions
27. glossaries
28. terminology
29. decision trees
30. calculators
31. comparison tools
32. myth-checking articles
33. evidence summaries
34. source library
35. translated guides
36. country-specific guides
37. local service directories
38. editorial proposals
39. community guide drafts
40. expert review
41. community review
42. version history
43. correction requests
44. outdated-content alerts
45. archived guidance
46. editorial standards
47. citation standards
48. guide ownership and maintainers

## 28. additional first-level category 42: families, children, schools, and education

stable key:

- `forum.families-education`

purpose:

teach safe, responsible, age-appropriate relationships with animals in families and educational settings

subcategories:

1. first family animal
2. family readiness
3. age-appropriate responsibilities
4. children and dogs
5. children and cats
6. children and birds
7. children and small animals
8. children and reptiles
9. children and farm animals
10. safe interaction
11. reading body language
12. bite prevention
13. scratch prevention
14. hygiene
15. allergies
16. pregnancy and animals
17. new babies and animals
18. toddlers and animals
19. school-age children
20. teenagers and animal care
21. family rules
22. chores
23. supervision
24. teaching empathy
25. teaching consent and boundaries
26. animal-related fears
27. child grief after animal loss
28. family disagreements
29. school animal programs
30. classroom animals
31. welfare of classroom animals
32. school visits
33. therapy-animal school visits
34. reading programs
35. educational farms
36. animal clubs
37. youth volunteering
38. educational materials
39. lesson plans
40. science projects
41. ethical observation
42. preventing cruelty
43. reporting concerning behavior
44. camps and activities
45. family success stories
46. educator questions

## 29. additional first-level category 43: local, regional, country, and language communities

stable key:

- `forum.local-communities`

purpose:

create location-aware and language-aware communities without duplicating every global forum topic

subcategories must be generated dynamically from the existing location and language taxonomies and support:

1. country communities
2. state or province communities
3. region communities
4. municipality communities
5. city communities
6. district communities
7. local language communities
8. newcomer questions
9. local veterinarians
10. local emergency clinics
11. local trainers
12. local groomers
13. local shelters
14. local rescues
15. local pet sitters
16. local walkers
17. local boarding
18. local pet-friendly places
19. local walking routes
20. local events
21. local meetups
22. local adoption
23. local lost and found
24. local volunteer requests
25. local supply assistance
26. local travel rules
27. local animal laws
28. local disease alerts
29. local parasite alerts
30. local weather preparation
31. local wildlife
32. local product availability
33. translation help
34. local announcements
35. local community moderation
36. local guides

do not create millions of empty physical categories

use virtual location pages, filters, or activity-based category creation when this better fits the architecture

## 30. additional first-level category 44: animal history, culture, media, and heritage

stable key:

- `forum.animal-history-culture`

purpose:

support educational and critical discussion about the role of animals in history, culture, media, art, and society

subcategories:

1. history of domestication
2. history of companion animals
3. breed history
4. species history
5. working-animal history
6. veterinary history
7. training history
8. animal welfare history
9. shelter and rescue history
10. animal-law history
11. cultural attitudes
12. naming traditions
13. folklore
14. mythology
15. literature
16. poetry
17. visual art
18. photography
19. film
20. television
21. documentaries
22. animation
23. games
24. journalism
25. social media
26. famous historical animals
27. memorial traditions
28. museums
29. archives
30. historical photographs
31. oral histories
32. traditional care practices
33. outdated and harmful practices
34. ethical media representation
35. misinformation in media
36. animal stereotypes
37. conservation communication
38. educational reviews
39. book discussions
40. film discussions
41. community research projects

## 31. category overlap and canonical-placement rules

some subjects can appear relevant to several categories

do not solve this by duplicating identical categories and content

implement:

- one canonical category for every topic
- multiple structured tags
- optional species links
- optional breed links
- optional location links
- optional topic-type links
- related-category links
- category aliases
- category redirects
- cross-category discovery
- recommended related discussions

when moving a topic:

- preserve its id
- preserve replies
- preserve reactions
- preserve subscriptions
- preserve bookmarks
- preserve attachments
- preserve reports
- preserve moderation history
- record the move
- notify the author according to notification preferences
- preserve an old-url redirect where applicable

## 32. multi-dimensional karma and reputation system

implement a complete reputation system

do not implement one simplistic global integer that rewards only activity volume

the system must support separate dimensions such as:

- helpfulness
- answer quality
- reliability
- evidence quality
- empathy
- respectful communication
- community support
- species-specific experience
- category-specific expertise
- local knowledge
- rescue contribution
- lost-and-found contribution
- adoption support
- mentoring
- guide contribution
- correction contribution
- moderation contribution
- marketplace trust
- service-review reliability
- event reliability
- professional contribution

reputation must be based on an immutable or append-only ledger

each reputation event must record:

- id
- user id
- reputation dimension
- event type
- source entity type
- source entity id
- actor id when applicable
- amount or weight
- reason code
- human-readable explanation translation key
- category scope
- species scope
- location scope where relevant
- status
- reversal relationship
- moderation case relationship
- created timestamp
- effective timestamp
- expiration or review timestamp where relevant
- metadata

support reputation-event reversal instead of silently editing history

examples of reputation-producing events may include:

- answer accepted by the topic author
- answer accepted through qualified community consensus
- verified correction
- useful guide contribution
- verified lost-animal sighting
- successful reunion contribution
- verified foster support
- approved translation contribution
- helpful peer-support response
- high-quality evidence contribution
- completed mentorship
- reliable marketplace transaction
- sustained rule-compliant participation

do not reward:

- raw post volume
- repetitive low-value replies
- reaction farming
- coordinated voting
- self-voting
- reciprocal voting rings
- purchasing
- advertising spending
- aggressive posting
- controversial content merely because it generates engagement

implement configurable daily and relationship-based limits

repeated votes from the same user to the same recipient must have diminishing or capped effect

deleted, fraudulent, plagiarized, manipulated, or moderation-removed content must reverse associated reputation events

reputation must never automatically prove that a user is:

- a veterinarian
- a lawyer
- a trainer
- a behaviorist
- a breeder
- a rescue organization
- a medical professional
- an animal welfare authority

professional verification must be separate

allow users to see:

- their own reputation ledger
- why reputation changed
- which dimensions changed
- which events were reversed
- how to appeal an incorrect event

public reputation display must be privacy-aware and configurable

do not publicly display humiliating negative scores

negative feedback can affect internal trust and moderation signals without creating public harassment

## 33. category-specific expertise

users must be able to build experience independently in different domains

examples:

- dog training
- cat nutrition
- aquarium water chemistry
- bird behavior
- reptile husbandry
- horse care
- farm-animal biosecurity
- lost-animal searches
- adoption support
- accessibility care

a user with high aquarium reputation must not automatically receive authority in veterinary medicine or legal discussions

store reputation scope using stable category, species, taxon, location, or capability identifiers

support:

- global reputation summary
- category reputation
- species reputation
- local-community reputation
- professional reputation
- marketplace reputation
- moderation trust

## 34. trust levels

implement configurable trust levels distinct from karma

possible trust levels:

- new member
- member
- established member
- trusted contributor
- mentor
- community reviewer
- category steward
- moderator
- senior moderator
- verified professional
- organization representative
- administrator

do not assume these exact names when the project has an existing role system

reuse existing roles and permissions where possible

trust progression may consider:

- account age
- verified email
- security status
- participation history
- rule compliance
- helpful contributions
- diversity of contributions
- successful appeals history
- report accuracy
- absence of manipulation
- completed onboarding
- category-specific reputation

no single metric may grant powerful moderation permissions

trust levels can be reduced after confirmed abuse

every trust-level change must have an audit trail

## 35. community confirmation and group consensus

implement a structured community-confirmation system

this system must not be a simple like counter

supported confirmation subjects may include:

- answer usefulness
- animal identification
- lost-animal sighting
- found-animal identity
- location information
- service availability
- event information
- product identity
- duplicate-topic classification
- guide accuracy
- translation accuracy
- local-rule information
- adoption or rescue organization identity
- non-medical factual corrections

confirmation states:

- not requested
- awaiting confirmation
- gathering evidence
- community supported
- community confirmed
- disputed
- insufficient evidence
- moderator reviewed
- expert reviewed
- outdated
- withdrawn
- rejected

confirmation records must support:

- subject type
- subject id
- claim text or structured claim
- scope
- requester
- evidence
- required quorum
- eligible voter rules
- confidence
- supporting votes
- opposing votes
- abstentions
- conflict-of-interest declarations
- review deadline when applicable
- expiration
- revalidation
- moderator decision
- expert review
- audit history

eligible community reviewers may be filtered by:

- account trust
- account age
- category experience
- species experience
- location relevance
- language
- prior report accuracy
- conflict-of-interest status
- active sanctions
- suspicious-account relationships

do not allow:

- self-confirmation
- duplicate-account confirmation
- household or organization vote stacking
- unlimited reciprocal confirmation
- newly created account brigading
- paid confirmation
- hidden sponsored confirmation
- confirmation by raw popularity alone

votes may be weighted, but the influence of one user must be capped

require diversity when appropriate

diversity may include:

- independent accounts
- different account creation periods
- different organizations
- different social clusters
- different locations when location is relevant
- a mix of trusted community users and qualified reviewers

community confirmation must expire or request revalidation when the underlying information may change

examples include:

- clinic opening hours
- local regulations
- event details
- travel requirements
- service availability
- product availability
- location accessibility

medical rules:

- community votes cannot confirm a medical diagnosis
- community votes cannot confirm medication dosage
- community votes cannot replace veterinary examination
- medical answers may be marked helpful, experience-based, moderator-reviewed, or verified-professional-authored
- professional authorship does not mean the platform guarantees the advice

legal rules:

- community votes cannot turn general legal information into formal legal advice
- jurisdiction must be visible
- outdated legal information must be revalidated

## 36. community review panels

support optional community review panels for low-risk moderation and classification tasks

examples:

- duplicate topic review
- wrong-category review
- tag review
- translation review
- guide clarity review
- identification confidence review
- non-sensitive content-quality review

community panels must not make final decisions for:

- threats
- child safety
- private personal data
- serious harassment
- animal cruelty evidence
- illegal trade
- professional credential fraud
- severe medical misinformation
- legal demands
- payment disputes requiring private evidence
- permanent account bans

panel requirements:

- random or balanced reviewer selection
- eligibility checks
- conflict checks
- limited private data
- anonymized evidence where possible
- independent voting
- reasoning field
- deadline
- replacement reviewer rules
- moderator override
- appeal path
- complete audit trail

## 37. accepted answers and solved topics

support:

- author-accepted answer
- multiple accepted answers when the problem has several valid parts
- community-supported answer
- community-confirmed factual answer
- expert-reviewed answer
- moderator-curated summary
- unresolved topic
- partially solved topic
- solved topic
- outdated solution
- reopened topic

accepting an answer must not prevent new corrections

a topic author cannot accept their own answer merely to generate reputation

when an accepted answer is deleted, invalidated, or significantly edited:

- recalculate the solved state
- reverse affected reputation when appropriate
- notify relevant users
- preserve history
- request another answer selection when needed

## 38. community notes and corrections

allow trusted users to propose contextual notes on forum content

community notes may be used for:

- outdated information
- missing context
- jurisdiction differences
- species-specific differences
- safety warnings
- source corrections
- translation corrections
- conflicts of interest
- sponsored-content disclosure
- product recall context
- duplicate-case context

note workflow:

1. note proposed
2. evidence attached
3. independent review
4. author response
5. community assessment
6. moderator review when required
7. note published, revised, rejected, or archived
8. note revalidated when information changes

notes must not become a harassment tool

authors cannot remove an approved safety note merely because they dislike it

moderators must not secretly rewrite a user note without version history

## 39. reactions and emotional feedback

support configurable reactions appropriate for an animal-owner community

possible reactions:

- helpful
- thank you
- supportive
- empathetic
- insightful
- well explained
- useful experience
- good source
- celebration
- hope
- reunited
- welcome
- caution
- needs clarification

avoid reactions designed mainly for ridicule or dogpiling

reaction rules:

- no self-reaction
- unique reaction constraints
- reversible reactions
- rate limiting
- abuse detection
- aggregate counters
- detailed user list visibility controlled by privacy settings
- notification preferences
- reputation effect separated from visual count
- suspicious reaction networks ignored pending review

## 40. voting and ranking

support optional quality voting where appropriate

separate:

- content quality
- agreement
- factual confirmation
- emotional support
- report
- professional review

a user disagreeing with an opinion must not automatically classify it as low quality

downvotes or negative-quality votes must:

- require an optional or mandatory reason according to category risk
- be rate limited
- avoid public humiliation
- not trigger automatic deletion
- be protected against coordinated attacks
- be reversible
- be excluded when fraudulent

ranking algorithms must use:

- time
- unique participants
- quality signals
- trust diversity
- spam signals
- report status
- category relevance
- user preferences

do not rank content only by total reactions

## 41. badges and achievements

implement badges as separate from professional credentials

badge groups may include:

- onboarding
- helpful contributor
- detailed answer
- evidence contributor
- guide author
- guide reviewer
- translator
- mentor
- foster supporter
- rescue volunteer
- lost-animal search supporter
- successful reunion contributor
- adoption supporter
- senior-animal supporter
- special-needs supporter
- local guide
- event organizer
- accessibility contributor
- community reviewer
- category steward
- marketplace reliability
- long-term constructive member

badge requirements:

- clear criteria
- no hidden paid path
- revocation rules
- expiration where appropriate
- versioned criteria
- progress visibility
- privacy settings
- moderation review for sensitive badges
- no badge solely for creating excessive posts

## 42. mentorship system

implement optional peer mentorship

mentorship types may include:

- first-time owner
- new species owner
- adoption adaptation
- foster support
- training support
- senior-animal care
- special-needs care
- aquarium setup
- terrarium setup
- horse ownership
- farm-animal care
- lost-animal search
- volunteer onboarding

mentor matching may use:

- species
- category experience
- language
- location
- time availability
- communication preference
- reputation
- trust level
- verified expertise where relevant

mentorship workflow:

1. mentor opts in
2. mentor defines supported scopes
3. user requests mentorship
4. system checks safety restrictions
5. mentor accepts or declines
6. both users receive boundaries and safety guidance
7. communication occurs through supported platform tools
8. either party may end mentorship
9. either party may block or report
10. completion feedback is optional
11. reputation is granted only after abuse-resistant validation

mentors must not be presented as medical, legal, or professional authorities unless separately verified

## 43. topic types and structured schemas

support configurable topic types such as:

- discussion
- question
- case
- journal
- guide
- urgent request
- emergency alert
- lost animal
- found animal
- sighting
- adoption listing
- foster request
- volunteer request
- service review
- product review
- place review
- event
- poll
- comparison
- checklist
- marketplace listing
- support request
- correction request
- identification request
- research discussion
- organization announcement

each topic type may define:

- required fields
- optional fields
- validation
- category restrictions
- permissions
- moderation level
- expiration behavior
- archival behavior
- location requirements
- species requirements
- contact restrictions
- allowed attachments
- allowed reactions
- confirmation availability
- accepted-answer availability
- seo rules
- privacy rules
- notification rules

do not build uncontrolled database columns for every topic type

use a maintainable schema approach compatible with the existing project, such as validated structured json backed by versioned schemas, dedicated tables for high-value entities, or a carefully designed combination

## 44. animal and pet context on forum topics

allow a topic to reference:

- no animal
- one pet profile
- multiple pet profiles
- one taxon
- multiple taxa
- one species
- one breed
- multiple breeds
- unknown animal
- unidentified animal

when linking a private pet profile:

- show only fields allowed by the pet owner
- do not expose private medical records
- do not expose exact home addresses
- do not expose private ownership documents
- store a safe topic-time snapshot when historical context is required
- respect later privacy changes

structured topic context may include:

- species
- breed
- age or age range
- sex
- reproductive status
- approximate weight
- relevant conditions
- location scope
- environment
- life stage

do not force users to reveal sensitive data

## 45. content authoring and editing

implement a high-quality authoring system using the existing architecture

support where appropriate:

- drafts
- automatic draft saving
- manual draft saving
- preview
- safe rich text or markdown
- headings
- lists
- quotations
- code where relevant
- tables
- checklists
- mentions
- tags
- internal links
- citations
- images
- galleries
- safe videos
- documents
- alt text
- captions
- content warnings
- structured fields
- edit reason
- edit history
- version comparison
- rollback according to permissions
- concurrent-edit conflict detection
- author collaboration
- moderator annotations
- translation proposals

sanitize all user-generated content

protect link-preview or remote-media functionality against server-side request forgery

validate file type using actual content, not only the filename extension

## 46. collaborative guides and community wiki

support community-maintained guides separately from ordinary forum posts

guide workflow:

- draft
- submitted for review
- changes requested
- community reviewed
- expert reviewed where relevant
- published
- correction requested
- outdated
- archived
- replaced

guide features:

- maintainers
- contributors
- version history
- cited sources
- review dates
- locale versions
- jurisdiction scope
- species scope
- change proposals
- discussion page
- rollback
- protected sections
- editorial locks
- attribution
- export
- printable layout

do not automatically convert popular posts into official guides

## 47. search and discovery

provide search across:

- categories
- subcategories
- topics
- replies
- guides
- users where privacy allows
- pet profiles where privacy allows
- animals and taxa
- organizations
- services
- events
- lost-and-found cases
- adoption cases
- marketplace listings

filters may include:

- category
- subcategory
- topic type
- species
- taxon
- breed
- age
- location
- distance
- language
- date range
- solved state
- accepted answer
- community confirmation
- expert review
- unanswered
- no replies
- followed
- bookmarked
- media present
- event date
- lost-and-found status
- adoption status
- urgency
- author trust scope

support:

- exact search
- phrase search
- safe partial search
- aliases
- synonyms
- translated names
- scientific names
- common names
- taxonomic synonyms
- spelling tolerance when supported
- saved searches
- recent searches
- search history deletion
- empty states
- no-result suggestions
- related topics
- duplicate-topic suggestions

never expose restricted or private content through search indexes, snippets, counts, or autocomplete

## 48. duplicate detection and topic consolidation

before publishing, show probable duplicates based on:

- title
- content
- species
- category
- location
- structured topic fields
- active case status
- recent date range

duplicate detection must be advisory unless a moderator acts

support:

- marking as duplicate
- canonical topic
- merging topics
- moving selected replies
- preserving authorship
- preserving timestamps
- preserving attachments
- preserving reactions
- preserving subscriptions
- preserving reports
- preserving links
- creating redirects
- notifying participants
- audit history

lost-and-found duplicates require special care because multiple reports can contain additional sightings

## 49. personalized forum feed

build a transparent and user-controlled feed

feed inputs may include:

- followed categories
- followed topics
- followed users
- followed tags
- owned animal species
- selected interests
- location
- language
- unresolved questions
- local alerts
- saved searches
- recent participation

provide:

- latest
- following
- local
- unanswered
- solved
- trusted
- guides
- events
- urgent alerts
- species-specific feeds

show why an item is recommended

allow disabling personalization

do not use engagement optimization that promotes cruelty, conflict, misinformation, or dangerous advice

urgent alerts must have separate safety ranking

## 50. subscriptions, following, and notifications

allow following:

- category
- subcategory
- topic
- reply thread
- user
- organization
- tag
- taxon
- species
- breed
- location
- event
- lost-and-found case
- adoption case
- guide
- search query

notification events may include:

- new reply
- mention
- quote
- reaction
- accepted answer
- confirmation request
- confirmation result
- correction
- guide update
- moderator action
- report update
- appeal update
- lost-animal sighting
- case status change
- event change
- category announcement
- security alert

provide granular preferences

support existing notification channels without introducing unsupported infrastructure

support:

- mute
- temporary mute
- quiet hours
- immediate notification
- grouped notification
- on-demand digest
- unread state
- mark all as read
- notification retention
- direct link to context

## 51. bookmarks and collections

support:

- private bookmarks
- public collections
- private collections
- shared collections
- bookmark notes
- custom labels
- custom sort order
- collection search
- collection export
- collection privacy
- collaborative collections where appropriate

examples:

- senior cat care
- aquarium setup
- travel documents
- training resources
- lost-animal search plan
- adoption preparation

## 52. complete report and complaint system

implement a unified polymorphic report system

users must be able to report:

- topic
- reply
- private message where supported
- user profile
- pet profile
- image
- video
- attachment
- organization
- service listing
- service review
- product review
- marketplace listing
- event
- lost-and-found case
- adoption listing
- guide
- community note
- confirmation vote
- moderator action
- professional credential claim

report reasons must include at least:

1. spam
2. repetitive posting
3. irrelevant advertising
4. undisclosed sponsored content
5. affiliate-link disclosure failure
6. scam
7. phishing
8. malware
9. suspicious external link
10. impersonation
11. stolen account
12. fake organization
13. fake professional credentials
14. fake rescue organization
15. fake shelter
16. fake breeder
17. adoption scam
18. lost-animal scam
19. reward scam
20. fundraising fraud
21. marketplace fraud
22. non-delivery
23. counterfeit product
24. prohibited product
25. prohibited animal sale
26. illegal wildlife trade
27. suspected poaching
28. animal cruelty
29. animal neglect
30. glorification of cruelty
31. dangerous handling
32. dangerous training advice
33. dangerous medical advice
34. medication misuse
35. false emergency claim
36. severe health misinformation
37. legal misinformation
38. public-health misinformation
39. product-safety misinformation
40. harassment
41. targeted harassment
42. bullying
43. threats
44. stalking
45. hate speech
46. discriminatory content
47. sexual harassment
48. unwanted contact
49. doxxing
50. private-address exposure
51. private-phone exposure
52. private-email exposure
53. identity-document exposure
54. private medical information
55. child safety
56. graphic injury without warning
57. graphic death without warning
58. cruelty imagery
59. copyright infringement
60. stolen image
61. plagiarism
62. misinformation
63. manipulated evidence
64. fabricated review
65. review bombing
66. coordinated voting
67. karma manipulation
68. fake confirmation
69. conflict of interest
70. moderator conflict
71. abuse of authority
72. wrong category
73. duplicate topic
74. misleading title
75. missing content warning
76. translation abuse
77. deliberate language disruption
78. off-topic content
79. prohibited personal transaction
80. suspicious animal sourcing
81. unsafe rehoming
82. irresponsible breeding advertisement
83. false lost-animal sighting
84. false animal identification
85. dangerous location information
86. outdated critical information
87. violation of community rules
88. other

report form requirements:

- subject
- reason
- optional subreason
- description
- urgency
- affected user
- affected animal where relevant
- location where relevant
- evidence
- screenshots
- private attachments
- immediate-safety indicator
- desired contact preference
- block-user option
- confirmation that information is truthful

do not expose reporter identity to the reported user by default

moderators may access only the information required for their role

## 53. report workflow

report states:

- draft
- submitted
- received
- duplicate-detected
- merged
- triaged
- awaiting-review
- assigned
- awaiting-reporter-information
- awaiting-reported-user-response
- escalated
- urgent-action-taken
- actioned
- no-violation-found
- insufficient-evidence
- referred-to-specialist-team
- appealed
- appeal-review
- reopened
- resolved
- closed
- retained-for-pattern-analysis

priority levels:

- critical
- high
- standard
- low

critical examples may include:

- credible threats
- immediate animal cruelty
- child safety
- exposed private location
- illegal wildlife transaction
- dangerous emergency misinformation
- account compromise
- coordinated fraud

critical priority must not automatically prove the report is true

implement:

- triage rules
- assignment
- reassignment
- moderator notes
- evidence log
- immutable event history
- user communication
- action recommendations
- action recording
- appeal linkage
- duplicate report grouping
- repeat-offender signals
- coordinated-report-abuse detection
- privacy controls
- retention controls

## 54. moderation cases and actions

separate reports from moderation cases

several reports may belong to one case

one report may be closed without opening a case

moderation actions may include:

- no action
- educational notice
- request for clarification
- content warning
- edit request
- moderator redaction
- sensitive-data removal
- content relocation
- duplicate merge
- content lock
- temporary content hiding
- content removal
- reaction removal
- reputation reversal
- confirmation cancellation
- badge revocation
- warning
- temporary posting limit
- temporary reply limit
- upload restriction
- private-message restriction
- marketplace restriction
- category-specific restriction
- local-community restriction
- temporary suspension
- permanent suspension
- organization restriction
- professional badge suspension
- emergency account protection
- forced password reset
- referral to platform legal or safety process

each action must record:

- legal or policy basis
- rule id
- evidence
- actor
- target
- scope
- start
- end
- review date
- user-visible reason
- internal reason
- appeal availability
- reversal
- related case

avoid hidden punishments

when a restriction affects a user, clearly show the relevant restriction unless disclosure creates a documented safety risk

## 55. appeals

allow appeals for eligible moderation actions

appeal workflow:

1. user receives action and reason
2. user sees appeal eligibility
3. user submits one structured appeal
4. original moderator is excluded from being the only appeal reviewer
5. appeal is reviewed by an authorized reviewer
6. additional evidence may be requested
7. action is upheld, modified, reversed, or returned for new review
8. reputation and content state are corrected when reversed
9. all events remain auditable
10. the user receives the result

protect against repetitive abusive appeals while preserving meaningful access to review

## 56. moderator conflicts and recusal

moderators must be able and required to recuse themselves when:

- personally involved
- closely connected to a party
- employed by an involved organization
- financially involved
- previously engaged in a public dispute
- responsible for the reported content
- unable to remain impartial

record recusal without publicly exposing unnecessary private details

## 57. moderation transparency

provide privacy-safe transparency information such as:

- report volumes
- report categories
- action types
- appeal volumes
- reversal rates
- average unresolved case age
- category-level safety trends
- coordinated abuse trends
- policy updates

do not publish private user identities or sensitive case details

## 58. block, mute, and contact safety

support:

- block user
- mute user
- mute topic
- mute category
- mute tag
- mute organization
- hide content from blocked users
- prevent direct contact
- prevent mention notifications
- prevent invitation notifications
- report while blocking
- review blocked-user list
- unblock

blocking must not:

- hide public moderation evidence from authorized moderators
- remove required ownership or transaction records
- break existing reports
- expose the blocker through a notification

## 59. professional verification

professional verification must be independent from karma

verification types may include:

- veterinarian
- veterinary technician
- trainer
- behaviorist
- groomer
- nutrition professional
- rehabilitation professional
- farrier
- wildlife rehabilitator
- rescue organization
- shelter
- breeder
- lawyer
- insurance representative
- animal transport provider
- researcher
- organization representative

verification must support:

- jurisdiction
- credential type
- credential identifier
- issuing organization
- issue date
- expiration date
- verification date
- reviewer
- status
- scope
- private evidence
- public-safe summary
- renewal
- suspension
- revocation
- appeal
- audit trail

credential documents must never be publicly exposed

expired verification must not remain visually identical to active verification

## 60. service reviews

service reviews must support:

- service category
- provider
- location
- visit or service date
- service received
- factual description
- price range
- positives
- problems
- resolution attempt
- recommendation
- sponsored relationship
- conflict of interest
- evidence available privately
- provider response
- moderation status
- appeal status

protect against:

- fabricated reviews
- competitor attacks
- mass review campaigns
- extortion
- doxxing employees
- unsupported accusations
- disclosure of private medical records
- duplicate reviews
- paid undisclosed reviews

providers may respond but cannot remove criticism merely because it is negative

## 61. marketplace trust and transactions

marketplace trust must remain separate from general forum karma

support:

- listing state
- reservation
- transaction initiation
- completed transaction
- cancelled transaction
- dispute
- refund state when relevant
- buyer feedback
- seller feedback
- evidence
- scam report
- delivery confirmation
- local pickup confirmation

only verified completed interactions may affect marketplace reputation

enforce prohibited-item and prohibited-animal policies

do not allow trade that violates:

- animal welfare requirements
- wildlife protection
- local law
- platform restrictions
- payment-provider restrictions

## 62. advanced lost-and-found case system

implement lost-and-found as structured cases rather than ordinary unstructured posts only

support:

- unique case number
- lost, found, sighted, stolen, and reunited states
- animal profile snapshot
- species
- breed
- sex
- age
- size
- color
- markings
- photos
- missing date
- missing time
- last known location
- approximate public location
- restricted exact location
- search radius
- map
- microchip status
- collar
- temperament
- medical needs
- contact relay
- reward information
- sightings
- sighting confirmation
- volunteer assignment
- poster generation
- printable qr code
- share page
- duplicate-case detection
- shelter contacts
- clinic contacts
- search timeline
- status history
- reunited confirmation
- case closure
- privacy-safe archive

exact private addresses and personal contact details must not be exposed by default

false sightings and reward scams must have dedicated report paths

## 63. adoption, foster, and rescue case management

support:

- animal listing
- organization or private-person type
- identity verification
- location
- species
- breed
- age
- sex
- sterilization
- vaccination
- microchip
- health conditions
- behavior
- compatibility
- special requirements
- adoption fee
- fee explanation
- transport
- application form
- application stages
- applicant screening
- home check
- references
- meeting
- reservation
- contract
- adoption
- trial period
- follow-up
- return
- failed adoption
- foster placement
- foster transfer
- case closure
- privacy
- report
- audit history

do not publish applicants' private information

## 64. emergency mode

support emergency topic types and safety banners

emergency topics may include:

- immediate veterinary emergency
- poisoning
- missing animal
- animal cruelty
- evacuation
- urgent foster
- blood donor request
- disease outbreak
- dangerous product alert
- weather emergency

requirements:

- clear emergency disclaimer
- country or location context
- configurable local emergency contact information
- urgent visual treatment without inaccessible flashing
- rapid report path
- duplicate alert detection
- source requirement for broad public alerts
- expiration
- update timeline
- resolution state
- abuse prevention

the platform must tell users to contact appropriate local professionals when immediate danger exists

## 65. groups and subcommunities

support forum groups where compatible with the social network

group visibility:

- public
- request-to-join
- private
- unlisted

group roles:

- owner
- administrator
- moderator
- steward
- member
- restricted member

group features:

- description
- rules
- species focus
- location focus
- language
- membership questions
- invitations
- join requests
- approvals
- member removal
- member bans
- group topics
- group events
- group guides
- group polls
- group files
- announcements
- reports
- moderation log
- ownership transfer
- closure
- archive

private groups must not appear in unauthorized search results, counts, feeds, or suggestions

## 66. polls and community decisions

support:

- single-choice poll
- multiple-choice poll
- ranked-choice poll
- anonymous poll
- visible-voter poll
- public-results poll
- results-after-vote
- results-after-close
- editable vote before close
- non-editable vote
- eligibility restrictions
- trusted-member poll
- location-limited poll
- group poll

poll closure should be derived safely from its configured closing timestamp and must not require a cron process merely to prevent later voting

polls must not be used as proof of medical, legal, or scientific truth

## 67. journals and progress tracking

support topic journals such as:

- training journal
- behavior journal
- recovery journal
- weight journal
- rehabilitation journal
- adoption adaptation journal
- foster journal
- aquarium journal
- terrarium journal
- pregnancy and newborn journal
- senior-care journal

journal features:

- entries
- dates
- structured measurements
- images
- milestones
- setbacks
- privacy
- selected collaborators
- comments
- progress charts where appropriate
- export
- archive

avoid harmful gamification or shame when a user misses an update

## 68. events, attendance, and clubs

extend events with:

- organizer
- verified organizer status
- event type
- species
- location
- online or physical
- start and end
- capacity
- waitlist
- registration
- invitation
- attendance requirements
- vaccination requirements where lawful
- animal age restrictions
- accessibility
- cost
- refund policy
- cancellation
- updates
- attendee communication
- report
- post-event review
- photo consent
- animal welfare rules
- emergency contact plan

## 69. expert question sessions

support verified professional question sessions

features:

- verified host
- professional scope
- jurisdiction
- session topic
- scheduled question window
- question queue
- moderation
- answer status
- unanswered status
- source links
- session archive
- correction
- disclaimer
- report

do not present an expert session as individual medical diagnosis or formal legal representation

## 70. content lifecycle

topic lifecycle states may include:

- draft
- published
- pending moderation
- needs clarification
- open
- answered
- partially solved
- solved
- disputed
- outdated
- locked
- archived
- merged
- redirected
- removed
- restored

support:

- stale-content warning
- update request
- author update
- community update proposal
- reopening
- controlled bumping
- necropost warning
- automatic read-time state calculation
- manual archive
- category archive rules
- retention
- legal hold where required
- restore

do not delete user content merely because it is old

## 71. accessibility

the complete forum must support:

- keyboard navigation
- visible focus
- screen readers
- semantic headings
- form labels
- error summaries
- accessible dialogs
- accessible tables
- accessible pagination
- reduced motion
- sufficient contrast
- zoom
- readable typography
- alt text
- captions
- text alternatives
- non-color status indicators
- accessible maps with textual alternatives
- mobile accessibility
- touch target sizing
- accessible drag-and-drop alternatives

do not require drag-and-drop as the only method for sorting or uploading

## 72. multilingual behavior

reuse the existing translation architecture

translate all platform-controlled text, including:

- category names
- descriptions
- rules
- topic types
- field labels
- validation messages
- states
- report reasons
- moderation actions
- appeal states
- trust levels
- reputation dimensions
- badge names
- notifications
- accessibility text
- empty states
- search filters
- safety notices
- legal notices
- medical notices
- taxonomy interface labels

user-generated content may remain in the original language

when user-generated translation is supported:

- preserve the original
- identify translated content
- identify translation source
- allow correction
- do not silently replace the original
- do not translate private content without authorization

scientific names must not be translated

common names may have localized values with scientific-name fallback

## 73. optional artificial-intelligence assistance

when the project already supports or intentionally adds artificial-intelligence services, they may assist with:

- tag suggestions
- duplicate suggestions
- category suggestions
- structured-field extraction
- summary drafts
- translation drafts
- alt-text drafts
- report triage suggestions
- spam-risk suggestions
- species-identification suggestions
- outdated-content suggestions

requirements:

- feature flag
- transparent label
- human review
- original content preserved
- no autonomous medical diagnosis
- no autonomous legal conclusion
- no autonomous permanent ban
- no autonomous professional verification
- no autonomous animal-cruelty accusation
- confidence display where appropriate
- privacy review
- audit log
- fallback when service is unavailable

## 74. administration interface

create or update a livewire administration area for:

- category tree
- category creation
- category editing
- category translation
- category ordering
- category visibility
- category permissions
- category rules
- category topic types
- category structured fields
- category moderation level
- category notices
- category redirects
- category aliases
- category archive
- category merge
- taxonomy synchronization
- seed-versus-database differences
- reputation configuration
- trust-level configuration
- badge configuration
- confirmation rules
- report reasons
- moderation actions
- notification templates
- guide workflow
- animal taxonomy sources
- import status
- import errors
- source version
- cache invalidation

seed-managed categories must be visibly distinguishable from administrator-created categories

administrators must receive a warning before changing immutable system keys

## 75. separate global animal taxonomy module

this module is not merely a forum feature

create or update a platform-wide animal taxonomy that can be used by:

- pet profiles
- forum topics
- social posts
- groups
- marketplace
- adoption
- lost and found
- events
- services
- search
- recommendations
- moderation
- analytics
- future modules

do not create a separate incompatible species list for each feature

do not create millions of forum categories

the scientific taxonomy must be a separate reusable domain

forum pages may link to any taxon through relations and filters

taxon community pages may be virtual and generated dynamically

a physical forum category for an individual species or breed should be created only when justified by activity, curation, or administrator approval

## 76. scientific-taxonomy source strategy

a manually written static list cannot be treated as complete species-level coverage

implement a versioned import architecture

preferred source roles:

- a broad authoritative catalogue as the primary taxonomic snapshot
- a biodiversity species service for matching and cross-references
- a marine-species source for marine enrichment
- a biodiversity-data standard for field mapping

before importing:

- verify source availability
- verify licensing
- verify attribution requirements
- record source version
- record download date
- record checksum
- preserve source metadata
- document conflict-resolution rules

the import must work from a local versioned snapshot after download

do not make every normal application request depend on an external taxonomy api

external services may be used for administrator-triggered synchronization, matching, or enrichment

## 77. global animal-taxonomy hierarchy

support all taxonomic ranks provided by the source, including where available:

- domain
- kingdom
- subkingdom
- infrakingdom
- superphylum
- phylum
- subphylum
- infraphylum
- superclass
- class
- subclass
- infraclass
- superorder
- order
- suborder
- infraorder
- parvorder
- superfamily
- family
- subfamily
- tribe
- subtribe
- genus
- subgenus
- section
- species group
- species
- subspecies
- variety
- form
- population
- strain
- morph
- hybrid
- unranked clade

do not hardcode a closed rank enum when the imported source can contain additional valid ranks

use a controlled value object or extensible rank registry

## 78. required high-level animal groups

the core high-level seed must include animalia and all major accepted high-level branches available in the selected source

at minimum support and display the following broad groups while importing every additional accepted group from the source

### chordates

- mammals
- birds
- reptiles
- amphibians
- ray-finned fishes
- lobe-finned fishes
- cartilaginous fishes
- jawless fishes
- tunicates
- lancelets
- other chordate groups present in the source

### mammals

support all imported mammal orders and families, including broad user-facing groupings for:

- monotremes
- marsupials
- primates
- carnivorans
- dogs and relatives
- cats and relatives
- bears
- mustelids
- seals and sea lions
- rodents
- lagomorphs
- bats
- hedgehogs, shrews, and moles
- even-toed ungulates
- whales and dolphins
- odd-toed ungulates
- elephants
- sirenians
- sloths and anteaters
- armadillos
- pangolins
- hyraxes
- aardvarks
- elephant shrews
- tenrecs and golden moles
- every remaining imported mammal group

### birds

support all imported bird orders, families, genera, species, and subspecies, including broad user-facing groupings for:

- parrots
- passerines
- finches
- canaries
- corvids
- pigeons and doves
- chickens and gamebirds
- ducks, geese, and swans
- birds of prey
- owls
- shorebirds
- cranes and rails
- gulls and terns
- woodpeckers
- kingfishers
- cuckoos
- hummingbirds and swifts
- penguins
- seabirds
- ratites
- every remaining imported bird group

### reptiles

support all imported reptile groups, including:

- turtles
- tortoises
- crocodilians
- tuatara
- lizards
- snakes
- amphisbaenians
- every remaining imported reptile group

### amphibians

support:

- frogs
- toads
- salamanders
- newts
- caecilians
- every remaining imported amphibian group

### fishes

support all imported fish taxa, including:

- freshwater fishes
- marine fishes
- brackish fishes
- ray-finned fishes
- cartilaginous fishes
- sharks
- rays
- chimaeras
- lobe-finned fishes
- lungfishes
- coelacanths
- lampreys
- hagfishes
- aquarium varieties
- aquaculture strains
- every remaining imported fish group

### arthropods

support:

- insects
- arachnids
- crustaceans
- myriapods
- horseshoe crabs
- sea spiders
- every remaining imported arthropod group

### insects

import every accepted insect order and lower taxon from the source

provide user-facing groupings for:

- beetles
- butterflies
- moths
- bees
- wasps
- ants
- flies
- mosquitoes
- true bugs
- cicadas
- aphids
- grasshoppers
- crickets
- katydids
- cockroaches
- termites
- mantises
- stick insects
- leaf insects
- dragonflies
- damselflies
- mayflies
- stoneflies
- earwigs
- lacewings
- antlions
- caddisflies
- fleas
- lice
- thrips
- silverfish
- bristletails
- webspinners
- zorapterans
- scorpionflies
- snakeflies
- alderflies
- dobsonflies
- twisted-wing parasites
- ice crawlers
- heelwalkers
- every remaining imported insect group

### arachnids

support:

- spiders
- scorpions
- mites
- ticks
- harvestmen
- pseudoscorpions
- camel spiders
- whip spiders
- whip scorpions
- short-tailed whip scorpions
- palpigrades
- ricinuleids
- every remaining imported arachnid group

### crustaceans

support:

- crabs
- true crabs
- hermit crabs
- lobsters
- crayfish
- shrimp
- prawns
- krill
- isopods
- amphipods
- copepods
- ostracods
- branchiopods
- fairy shrimp
- tadpole shrimp
- water fleas
- barnacles
- mantis shrimp
- remipedes
- cephalocarids
- every remaining imported crustacean group

### myriapods

support:

- centipedes
- millipedes
- symphylans
- pauropods
- every remaining imported myriapod group

### molluscs

support:

- gastropods
- terrestrial snails
- aquatic snails
- slugs
- bivalves
- clams
- mussels
- oysters
- scallops
- cephalopods
- octopuses
- squids
- cuttlefish
- nautiluses
- chitons
- tusk shells
- monoplacophorans
- aplacophorans
- every remaining imported mollusc group

### annelids

support:

- earthworms
- leeches
- polychaetes
- bristle worms
- tube worms
- every remaining imported annelid group

### cnidarians

support:

- corals
- sea anemones
- jellyfish
- hydroids
- siphonophores
- every remaining imported cnidarian group

### echinoderms

support:

- sea stars
- brittle stars
- sea urchins
- sand dollars
- sea cucumbers
- crinoids
- every remaining imported echinoderm group

### sponges

support every imported sponge group

### additional animal phyla and high-level groups

support and import all accepted taxa belonging to groups including:

- comb jellies
- flatworms
- roundworms
- ribbon worms
- rotifers
- thorny-headed worms
- tardigrades
- velvet worms
- bryozoans
- brachiopods
- phoronids
- hemichordates
- xenacoelomorphs
- placozoans
- gastrotrichs
- kinorhynchs
- priapulids
- loriciferans
- horsehair worms
- arrow worms
- entoprocts
- cycliophorans
- gnathostomulids
- micrognathozoans
- dicyemids
- orthonectids
- every other accepted animal phylum, class, order, family, genus, species, and lower rank available in the selected versioned source

the phrase `every other accepted group` is not permission to ignore them

they must be imported from the source and represented in the requirements and import-verification reports

## 79. domestic animal and breed taxonomy

scientific taxonomy and domestic breed taxonomy are related but different

implement separate support for:

- domestic species
- breed group
- breed
- variety
- type
- landrace
- strain
- color variety
- coat variety
- morph
- hybrid
- registry classification

support breeds and varieties for:

- dogs
- cats
- rabbits
- horses
- ponies
- donkeys
- cattle
- sheep
- goats
- pigs
- chickens
- ducks
- geese
- turkeys
- pigeons
- companion birds
- fish
- shrimp
- reptiles
- amphibians
- insects
- other domesticated or captive-bred animals where valid data exists

do not force all animal varieties into a misleading `breed` field

use the appropriate classification type

support multiple registry names and aliases

do not use one registry's identifier as the permanent internal primary key

## 80. animal-taxonomy database concepts

inspect the current schema before creating new tables

support concepts equivalent to:

### taxon source

- id
- stable key
- name
- source type
- version
- release date
- download date
- checksum
- license
- attribution
- source url stored in data
- import priority
- active state
- metadata

### taxon

- internal id
- stable internal key
- parent id
- rank
- scientific name
- canonical name
- authorship
- nomenclatural code
- taxonomic status
- accepted taxon id
- original taxon id where available
- source id
- source record id
- depth
- materialized path or equivalent optimized hierarchy field
- extinct state
- fossil state
- marine state where sourced
- freshwater state where sourced
- terrestrial state where sourced
- domestic relevance
- community relevance
- active state
- metadata
- created timestamp
- updated timestamp
- archived timestamp

### taxon name

- taxon id
- locale
- name
- name type
- preferred state
- source
- source record id
- geographic scope
- language
- script
- verified state
- metadata

name types may include:

- scientific
- canonical
- common
- preferred common
- alternate common
- historical
- synonym
- misspelling
- trade name
- breed registry name
- local name

### taxon external identifier

- taxon id
- source
- external identifier
- external url metadata where safe
- identifier type
- active state
- version

### taxon import

- source
- source version
- state
- started
- completed
- current chunk
- processed rows
- inserted rows
- updated rows
- unchanged rows
- synonym rows
- archived rows
- error rows
- warning rows
- checksum
- error report
- resume token or cursor
- initiated by
- metadata

### taxon change history

record:

- addition
- update
- rename
- rank change
- parent change
- synonym change
- accepted-name change
- merge
- split
- archive
- restoration
- source conflict
- local override

## 81. taxonomic synonyms, merges, and splits

never delete a taxon merely because the source changes its accepted name

support:

- accepted taxon
- synonym
- ambiguous synonym
- misapplied name
- historical name
- invalid name
- unresolved name
- placeholder taxon
- merged taxon
- split taxon
- archived taxon

when a taxon is merged:

- preserve the old internal id
- redirect selection to the accepted taxon
- preserve existing pet relations
- preserve forum relations
- preserve search matching
- record history

when a taxon is split:

- do not automatically guess the correct new taxon for existing user animals
- mark existing relations as requiring review when necessary
- preserve the original historical selection
- allow the owner or moderator to select the corrected taxon
- record the decision

## 82. source conflict resolution

different sources can disagree

implement documented source-priority and conflict rules

do not silently overwrite curated local data

support:

- primary source
- supplementary source
- local curated override
- disputed classification
- unresolved match
- manual review
- source-specific classification view where useful

store source provenance for every imported name and important classification

## 83. animal taxonomy import process

the full import must be:

- idempotent
- versioned
- chunked
- resumable
- transaction-safe at an appropriate chunk level
- memory bounded
- observable
- auditable
- restartable
- recoverable
- protected from duplicate execution
- protected from partial corruption

before applying a new source version:

1. validate file integrity
2. validate expected columns
3. validate source version
4. validate license metadata
5. run a dry analysis
6. calculate additions
7. calculate updates
8. calculate parent changes
9. calculate accepted-name changes
10. calculate merges
11. calculate splits
12. calculate removals
13. calculate unresolved references
14. create an impact report
15. require appropriate authorization
16. import in safe chunks
17. rebuild derived paths or indexes
18. validate hierarchy
19. validate orphan counts
20. validate cycle counts
21. clear relevant caches
22. record completion

do not hold one enormous database transaction for millions of rows

do not expose a half-imported taxonomy as the active version

use staging tables, import versions, activation flags, or another safe architecture

## 84. core animal seed and full taxonomy data

create separate mechanisms for:

### core taxonomy seed

small, production-safe, idempotent seed containing:

- animalia
- essential high-level ranks
- major user-facing animal groups
- common domestic species
- stable system keys
- basic community grouping

### full taxonomy import

versioned data import containing all available taxa from the approved source snapshot

### demo animal seed

development-only data containing:

- example species
- example breeds
- example pet profiles
- example forum relations
- example lost cases
- example adoption cases

do not place millions of manually typed species rows inside one php seeder class

## 85. community-friendly animal grouping

scientific taxonomy must remain scientifically structured

also create a separate community grouping layer for navigation

possible user-facing groups:

- companion dogs
- companion cats
- companion small mammals
- rabbits
- rodents
- ferrets
- companion birds
- poultry
- horses and equines
- farm mammals
- aquarium freshwater fish
- aquarium marine fish
- pond fish
- aquarium crustaceans
- aquarium molluscs
- corals and marine invertebrates
- reptiles
- amphibians
- arachnids
- insects
- myriapods
- terrestrial molluscs
- other terrestrial invertebrates
- wildlife
- rescue wildlife
- working animals
- assistance animals
- exotic animals
- unidentified animals
- other animals

these groupings must reference taxa

they must not duplicate or replace the scientific hierarchy

## 86. animal selection interface

create reusable livewire animal taxonomy selectors

support:

- scientific-name search
- common-name search
- translated common-name search
- synonym search
- breed search
- hierarchical browsing
- recent selections
- popular selections
- domestic-animal shortcuts
- aquarium shortcuts
- terrarium shortcuts
- bird shortcuts
- horse shortcuts
- farm-animal shortcuts
- exact taxon selection
- higher-rank selection
- unknown species
- unidentified animal
- request taxonomy review

show:

- preferred common name
- scientific name
- rank
- parent context
- ambiguity warnings
- accepted-name status
- synonym redirect
- source where appropriate

do not load the entire taxonomy tree into the browser

## 87. taxonomy performance

provide indexes suitable for:

- parent lookup
- stable key
- scientific name
- canonical name
- normalized name
- source record
- accepted taxon
- rank
- active state
- path
- import version

use:

- bounded pagination
- selective columns
- cached high-level trees
- search indexes where existing infrastructure supports them
- bulk upsert
- chunked processing
- precomputed hierarchy paths when justified
- cache invalidation after activation

measure:

- common-name search
- scientific-name search
- hierarchical navigation
- pet-profile selection
- forum topic filtering
- import throughput
- memory use
- query count

## 88. taxonomy translations

do not create machine-translated common names as verified facts

support:

- imported common names
- locally curated names
- locale
- language
- script
- geographic scope
- source
- preferred status
- verification status

fallback order may be:

1. verified preferred name for current locale
2. verified alternate name for current locale
3. project fallback-locale common name
4. scientific name

follow the existing project fallback architecture

## 89. taxonomy administration

administrators or authorized taxonomy curators must be able to:

- view import versions
- compare source versions
- inspect changes
- inspect errors
- resume import
- cancel safe pending import
- activate completed version
- roll back active version
- edit local common names
- add local aliases
- mark a common name inappropriate
- resolve source conflicts
- map breeds
- review unidentified requests
- merge local duplicates
- review taxon splits
- invalidate caches
- export reports

do not allow ordinary moderators to modify scientific taxonomy unless explicitly authorized

## 90. candidate data-model review

inspect and reuse existing entities before adding anything

the complete system may require concepts equivalent to:

- forum category
- category translation
- category rule
- category permission
- category alias
- category redirect
- topic
- topic type
- topic schema
- topic structured data
- reply
- reaction
- quality vote
- reputation ledger
- reputation aggregate
- trust level
- trust history
- badge
- user badge
- confirmation
- confirmation vote
- confirmation evidence
- community note
- community note review
- report
- report event
- moderation case
- moderation action
- appeal
- moderator recusal
- subscription
- notification preference
- bookmark
- collection
- guide
- guide version
- guide review
- mentorship
- mentor scope
- event
- attendance
- lost-and-found case
- sighting
- search volunteer
- adoption case
- adoption application
- marketplace transaction
- service review
- professional verification
- taxon
- taxon name
- taxon source
- taxon import
- taxon change
- breed
- breed registry
- community animal group

do not create every candidate table blindly

perform domain modeling first

reuse existing models where they already satisfy the requirement cleanly

## 91. database correctness and concurrency

add appropriate:

- foreign keys
- unique indexes
- compound indexes
- check constraints where supported
- enum casts
- json casts
- timestamps
- soft deletion or archival
- optimistic locking where editing conflicts matter
- transactions
- row locking where vote or state races matter
- idempotency keys
- database-level uniqueness for one-vote rules

test concurrent scenarios for:

- double voting
- double reaction
- simultaneous answer acceptance
- simultaneous report action
- duplicate confirmation
- double badge grant
- double reputation event
- concurrent category synchronization
- concurrent taxonomy import activation
- simultaneous case closure

## 92. authorization

define server-side abilities for all sensitive operations

examples:

- view category
- view restricted category
- create topic
- reply
- edit own content
- edit another user's content
- delete own content
- request deletion
- restore content
- upload media
- create poll
- create lost case
- view exact lost-case location
- create adoption listing
- review adoption application
- vote
- confirm
- review confirmation
- propose community note
- publish community note
- report
- view report
- triage report
- moderate category
- apply moderation action
- hear appeal
- verify professional
- manage categories
- manage reputation rules
- manage badges
- manage taxonomy
- run taxonomy import
- activate taxonomy version

use policies and existing permission architecture

test direct endpoint and livewire-action access

## 93. caching and counters

cache stable data such as:

- category tree
- category translations
- category rules
- high-level animal taxonomy
- popular animal groups
- topic-type schemas
- report reason definitions
- badge definitions

do not cache permission-sensitive results without including permission context

provide deterministic invalidation for:

- category seed synchronization
- category admin change
- taxonomy version activation
- translation update
- permission change
- rule change
- guide publication
- topic move
- moderation action

avoid live count queries for every category card

use safe counters or aggregate queries

document counter consistency and repair behavior

## 94. seeds

create a production-safe forum system seed that:

- creates all required first-level categories
- creates every required subcategory
- uses stable keys
- updates system-managed metadata
- preserves ids
- preserves user content
- preserves admin-created categories
- archives obsolete seeded categories safely
- creates redirects
- logs conflicts
- can run repeatedly
- produces deterministic order

create separate seeds for:

- forum taxonomy
- topic types
- report reasons
- moderation action definitions
- reputation dimensions
- trust-level definitions
- badge definitions
- confirmation-state definitions
- core animal taxonomy
- community animal groups
- development demo data

do not mix fake forum content into the production taxonomy seed

## 95. migration and backfill

analyze existing content before moving it

produce:

- category mapping report
- topic-type inference report
- ambiguous-topic report
- legacy slug report
- duplicate-category report
- unsupported-field report
- lost-and-found conversion report
- adoption conversion report
- translation gap report
- animal-species mapping report

do not classify sensitive existing topics using title keywords alone

when confidence is insufficient:

- preserve the original relation
- mark for review
- show it in an admin migration queue
- do not delete the source data

every migration must be restartable or safely reversible

## 96. tests

create comprehensive automated tests linked to requirement ids

test at least:

### planning and traceability

1. every atomic requirement has a plan item
2. every implemented requirement has evidence
3. no category requirement is absent from the matrix
4. no feature requirement is absent from the matrix
5. no animal taxonomy requirement is absent from the matrix

### category seed

6. all original twenty categories exist
7. all new categories from twenty-one through forty-four exist
8. every required subcategory exists
9. stable keys are unique
10. slugs are valid
11. parent relationships are valid
12. ordering is deterministic
13. rerun creates no duplicates
14. rerun updates system metadata
15. rerun preserves ids
16. rerun preserves topics
17. rerun preserves replies
18. rerun preserves reactions
19. rerun preserves subscriptions
20. rerun preserves reports
21. admin categories are not overwritten
22. obsolete populated categories are archived
23. redirects work

### karma and trust

24. self-voting is rejected
25. duplicate voting is rejected
26. reciprocal abuse limits work
27. reputation event is recorded
28. event reversal works
29. deleted invalid content reverses reputation
30. category-specific reputation stays scoped
31. karma does not grant professional verification
32. public negative humiliation is not exposed
33. trust changes are audited
34. concurrent reputation events do not duplicate

### group confirmation

35. ineligible users cannot confirm
36. self-confirmation is rejected
37. conflict-of-interest rules work
38. quorum works
39. diversity rules work
40. suspicious votes are excluded
41. confirmation can be disputed
42. confirmation can expire
43. revalidation works
44. medical diagnosis cannot become community confirmed
45. legal advice cannot become formally verified by votes
46. concurrent votes remain consistent

### accepted answers

47. author can accept eligible answer
48. author cannot farm self-answer reputation
49. multiple accepted answers work where enabled
50. removed answer recalculates solved state
51. answer edit preserves history
52. invalid answer reverses reputation

### reports and moderation

53. every reportable entity can be reported
54. report privacy is enforced
55. reporter can block while reporting
56. duplicate reports can be grouped
57. critical report receives correct priority
58. critical priority does not auto-convict
59. unauthorized moderator cannot view case
60. moderator recusal works
61. moderation action is audited
62. action has user-visible reason
63. appeal excludes sole original reviewer
64. successful appeal restores state
65. report abuse is rate limited
66. private evidence is protected
67. permanent ban is not produced by an unreviewed automatic signal

### professional verification

68. credential evidence is private
69. expired verification changes public state
70. suspended verification is visible correctly
71. karma cannot create verification
72. unauthorized users cannot verify professionals

### lost and found

73. exact location is private by default
74. public approximate location works
75. sightings can be recorded
76. confirmation rules work
77. duplicate case detection works
78. reunion closes the case safely
79. closed case preserves history
80. contact relay hides private contact data

### adoption

81. private applicant data is protected
82. application state transitions are valid
83. unauthorized users cannot inspect applications
84. listing report works
85. adoption closure preserves audit history

### taxonomy

86. core taxonomy seed is idempotent
87. internal keys are stable
88. parent relations have no cycles
89. imported taxa have valid source records
90. scientific names are searchable
91. common names are searchable
92. synonyms resolve to accepted taxa
93. merged taxa preserve old relations
94. split taxa require review when ambiguous
95. import can resume
96. failed import does not become active
97. only one intended taxonomy version is active
98. rollback works
99. source checksum is recorded
100. source version is recorded
101. local overrides survive import
102. full import is memory bounded
103. taxonomy selector does not load the full tree
104. restricted taxonomy administration is enforced

### translations

105. every system category has translations
106. every report reason has translations
107. every state has translations
108. fallback works
109. scientific names are not translated
110. user-generated original content remains available

### performance and security

111. category browsing has no n+1 query pattern
112. taxonomy browsing has no n+1 query pattern
113. search does not expose private content
114. upload validation works
115. unsafe html is sanitized
116. remote preview is protected from server-side request forgery
117. rate limiting works
118. permission-sensitive cache does not leak
119. direct livewire action authorization works
120. database race conditions remain consistent

do not limit tests to this minimum

generate additional tests for every atomic acceptance criterion

## 97. documentation

document:

- complete category hierarchy
- new first-level categories
- stable-key conventions
- category overlap rules
- category redirects
- category synchronization
- topic types
- structured topic schemas
- karma and reputation
- trust levels
- group confirmation
- community review panels
- accepted answers
- community notes
- reactions
- badges
- mentorship
- reports
- moderation cases
- appeals
- professional verification
- lost-and-found workflow
- adoption workflow
- marketplace trust
- emergency mode
- groups
- guides
- search
- notifications
- privacy
- security
- cache behavior
- migrations
- rollback
- animal taxonomy
- source versions
- import process
- synonym handling
- merge handling
- split handling
- common-name translations
- breed taxonomy
- taxonomy administration
- testing
- operations
- recovery

update all relevant existing markdown files rather than creating disconnected documents

update the changelog

## 98. implementation phases

implement in this order

### phase 0: preserve and atomize the specification

- preserve source prompt
- create checksum
- extract requirements
- create requirements matrix
- create master plan

### phase 1: repository audit

- inspect models
- inspect migrations
- inspect seeds
- inspect factories
- inspect livewire
- inspect blades
- inspect translations
- inspect policies
- inspect moderation
- inspect search
- inspect cache
- inspect tests
- inspect deployment constraints

### phase 2: domain design

- design category domain
- design topic-type domain
- design reputation domain
- design trust domain
- design confirmation domain
- design moderation domain
- design taxonomy domain
- write architecture decisions

### phase 3: schema

- create only necessary migrations
- add constraints
- add indexes
- add state enums
- add casts
- prepare rollback

### phase 4: forum taxonomy

- preserve original categories
- add new categories
- add subcategories
- create synchronizer
- create redirects
- create migration map

### phase 5: global animal taxonomy

- core taxonomy
- source model
- import model
- name model
- synonym handling
- breed taxonomy
- community grouping
- import pipeline
- administration

### phase 6: reputation, trust, badges, and confirmation

- reputation ledger
- aggregates
- anti-abuse
- trust levels
- badges
- community confirmation
- review panels
- accepted answers
- community notes

### phase 7: reports and moderation

- reports
- case management
- actions
- appeals
- recusal
- audit
- transparency
- privacy

### phase 8: structured community functionality

- topic types
- structured fields
- lost and found
- adoption
- mentorship
- groups
- events
- polls
- journals
- guides
- marketplace trust

### phase 9: search, feed, subscriptions, and notifications

- search
- filters
- duplicate detection
- feed
- following
- notifications
- bookmarks
- collections

### phase 10: livewire interface

- public forum
- category tree
- topic creation
- topic pages
- reports
- confirmation
- reputation
- taxonomy selectors
- admin pages
- accessibility
- responsive behavior

### phase 11: migration and backfill

- analyze legacy data
- run safe mappings
- create review queues
- validate preservation
- produce reports

### phase 12: seeds and demo data

- production seeds
- core taxonomy seed
- feature-definition seeds
- development demo seeds
- rerun verification

### phase 13: tests

- feature tests
- unit tests
- policy tests
- seed tests
- concurrency tests
- privacy tests
- performance tests
- import tests

### phase 14: documentation and final audit

- update documentation
- update changelog
- inspect diff
- remove dead code
- verify translations
- verify requirements matrix
- verify every acceptance criterion
- produce final report

## 99. final completeness audit

before declaring completion:

1. reread the full preserved source prompt
2. compare every source bullet with the requirements matrix
3. verify every requirement id
4. verify every plan item
5. verify every changed file
6. verify every migration
7. verify every seed
8. verify every translation
9. verify every policy
10. verify every interface
11. verify every test
12. verify every documentation entry
13. search for placeholders
14. search for todo comments introduced by this work
15. search for hardcoded user-facing text
16. search for `@php` in blade
17. search for unauthorized direct model access in blade
18. search for n+1 patterns
19. search for destructive data operations
20. search for unstable name-based identifiers
21. run relevant formatting
22. run relevant automated tests
23. inspect failures
24. fix failures caused by the implementation
25. repeat the audit

the final completeness report must include:

- total number of source requirements
- total number of atomic requirements
- total planned
- total implemented
- total tested
- total documented
- total verified
- total blocked
- total intentionally not applicable
- percentage coverage
- missing requirement ids
- untested requirement ids
- undocumented requirement ids

completion requires no silently missing requirements

## 100. final implementation report

provide:

- repository architecture discovered
- assumptions
- decisions
- conflicts resolved
- files created
- files modified
- migrations created
- models created or updated
- actions created
- services created
- policies created
- livewire components created or updated
- blade views created or updated
- translations created or updated
- original category count
- new category count
- total first-level category count
- total subcategory count
- topic-type count
- report-reason count
- reputation-dimension count
- trust-level count
- badge count
- confirmation-rule count
- legacy categories mapped
- legacy topics migrated
- topics awaiting manual review
- animal taxonomy source
- animal taxonomy source version
- imported kingdom count
- imported phylum count
- imported class count
- imported order count
- imported family count
- imported genus count
- imported species count
- imported subspecies count
- imported synonym count
- imported common-name count
- unresolved taxon count
- breed count
- import warnings
- tests added
- tests executed
- test results
- query and performance observations
- cache changes
- security changes
- privacy changes
- compatibility notes
- rollback instructions
- recovery instructions
- remaining risks
- blocked requirements
- exact safe deployment steps

explicitly confirm whether existing:

- topics
- replies
- reactions
- votes
- subscriptions
- bookmarks
- reports
- moderation cases
- attachments
- pet profiles
- adoption cases
- lost-and-found cases
- marketplace records
- translations
- administrator-created categories

were preserved

do not state that everything is complete unless the final traceability audit proves it
</forum-source-extension>
