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

## Revision 2026-07-31: Pet Profile And Full Lifecycle

This dated revision is additive. Parts A and B above remain unchanged and
mandatory. The revision payload below is preserved verbatim from local Codex
history and is part of the indivisible master specification.

- Revision source timestamp: `1785514046`
- Revision raw payload SHA-256: `2f45d1f423e3ac0755db8b91aeea0c07315c19fb8e7f40647e3c068de5e256bc`
- Master raw payload SHA-256: `a7cde460775a0339e8a82490a41e9a9a557296a28846dfaadff4df17bad53717`
- Master checksum payload: Parts A and B checksum payload, two LF characters, exact revision payload

<pet-profile-source-revision>
# punkt 2 — profil pitomca, ego cifrovaja lichnost, vladenie, sovladelcy, privatnost, istoria i polnyj zivotnyj cikl

## 1 — glavnaja cel profilia pitomca

**cto eto dolzno delat**

profil pitomca dolzen byt centralnoj cifrovoj kartockoj zivotnogo, k kotoroj priviazyvajutsia ego socialnaja aktivnost, fotografii, druzia, sobytija, medkartocka, dnevnik uxoda, ustrojstva, dokumenty, poisk pri propazhe, adopcija, uslugi i dostup drugih liudej

**pocemu eto nuzno**

bez edinogo profilia informacija o pitomce budet razbrosana po postam, chatam, dokumentam, klinikam i akkauntam raznyx xoziaev, iz-za cego budut pojavljatsia dublikaty, protivorechija i poteri vaznyx dannyx

**kak eto dolzno rabotat po logike**

snachala sozdaetsia minimalnaja cifrovaja lichnost pitomca, posle cego k nej po mere neobxodimosti podkliucajutsia otdelnye moduli

* publicnyj profil
* socialnye sviazi
* medkartocka
* dnevnik uxoda
* gps i drugie ustrojstva
* dokumenty
* marketplace
* adopcija
* poisk pri propazhe
* professionalnye uslugi

**dlia kakoj celi eto delaetsia**

dlia sozdaniia odnogo stabilnogo istochnika informacii o konkretnom zivotnom na protyazenii vsej ego zizni

**kakoj rezultat dolzen byt dostignut**

u kazdogo pitomca est odin upravljaemyj profil, a vse funkcii socialnoj seti mogut bezopasno ssylatsia na nego bez sozdanija raznyx nesviazannyx kopij

---

## 2 — profil pitomca ne ravnjaetsia akkauntu celoveka

**cto eto dolzno delat**

sistema dolzna strogo razdeliat

* akkaunt realnogo celoveka
* publicnyj profil xoziajina
* profil pitomca
* professionalnyj profil
* profil organizacii

**pocemu eto nuzno**

pitomec ne moz et sam prinimat juridiceskie resenija, oplachivat uslugi, soglasatsia na obrabotku dannyx ili upravliat bezopasnostiu akkaunta

**kak eto dolzno rabotat po logike**

vse dejstvija ot imeni pitomca fakticeski vypolniaet konkretnyj upravliajuscij celovek

sistema dolzna vsegda znat

* kto vypolnil dejstvie
* ot imeni kakogo pitomca
* v kakoj roli
* imel li on pravo
* kogda eto proizoslo

**dlia kakoj celi eto delaetsia**

dlia iskljuceniia anonimnyx ili neotvetstvennyx dejstvij ot imeni zivotnogo

**kakoj rezultat dolzen byt dostignut**

drugie polzovateli vidat socialnyj obraz pitomca, no platforma moz et tocno ustanovit realnogo avtora kazdogo posta, soobsenija ili izmenenija

---

## 3 — odin realnyj pitomec i odin osnovnoj profil

**cto eto dolzno delat**

sistema dolzna stremitsia k tomu, ctob odnomu realnomu zivotnomu sootvetstvoval odin osnovnoj kanoniceskij profil

**pocemu eto nuzno**

neskolko profilej odnogo pitomca privodiat k

* razdelennoj medkartocke
* raznym mikrochipam
* dublikatam objavlenij o propazhe
* putanice mezhdu sovladelcami
* dvojnym napominanijam
* raznym spiskam druzej
* protivorechivym dokumentam
* oshibkam pri adopcii ili peredace

**kak eto dolzno rabotat po logike**

pri sozdan ii novogo profilia sistema proveriaet vozmoznye sovpadenija po

* xoziajinu
* imeni
* vidu
* fotografijam
* date rozdenija
* mikrochipu
* registracionnomu nomeru
* klinike
* predyduscim profiljam
* adopcionnym dokumentam

**dlia kakoj celi eto delaetsia**

dlia celostnosti istorii zivotnogo

**kakoj rezultat dolzen byt dostignut**

daze pri smene xoziajina, kliniki ili priuta osnovnaja istoria pitomca ne razryvaetsia na neskolko nesviazannyx profilej

---

## 4 — profil pitomca kak dolgosrocnaja cifrovaja lichnost

**cto eto dolzno delat**

profil dolzen soxran iat sviaz s pitomcem na protyazenii raznyx etapov

* rozdenie
* pervyj dom
* priut
* perederzka
* adopcija
* smena xoziajina
* putesestvija
* bolezni
* poisk pri propazhe
* pozhiloj vozrast
* memorialnyj rezhim

**pocemu eto nuzno**

zivotnoe moz et menjat mesto prozhivanija, xoziaev, specialistov i socialnye uslovija, no ostajotsia tem ze samym pitomcem

**kak eto dolzno rabotat po logike**

u profilia est stabilnyj vnutrennij identifikator, kotoryj ne meniaetsia pri smene publicnogo imeni, username, xoziajina ili statusa

**dlia kakoj celi eto delaetsia**

dlia soxraneniia nepreryvnoj istorii bez priviazki ko vremennomu socialnomu imeni ili odnomu akkauntu

**kakoj rezultat dolzen byt dostignut**

dannye pitomca ostajutsia celostnymi, a vaznye sobytija ne teriajutsia pri izmenenii ziznennoj situacii

---

## 5 — podderzka raznyx vidov zivotnyx

**cto eto dolzno delat**

profil dolzen podderzhivat ne tolko sobak i koshek, no i drugie vidy

* ptic
* krolikov
* gryzunov
* ryb
* reptilij
* amfibij
* loshadej
* selskoxoziajstvennyx zivotnyx
* ekzoticeskix zivotnyx
* drugie dopustimye vidy

**pocemu eto nuzno**

socialnaja set dlia xoziaev zivotnyx ne dolzna zastavliat polzovatelia vpisyvat popugaja, rybu ili reptiliju v formu, sozdannuju tolko dlia sobaki

**kak eto dolzno rabotat po logike**

posle vybora vida profil podkljucaet podxodiascie polia, kategorii, spravocniki, napominanija i integracii

naprimer dlia ryby ne nuzno pole dlina shersti, a dlia sobaki ne nuzno obyazatelnoe pole ph vody

**dlia kakoj celi eto delaetsia**

dlia korrektnogo i uvazitelnogo opisaniia kazdogo vida

**kakoj rezultat dolzen byt dostignut**

polzovatel vidit tolko relevantnye polia, a platforma ne sobiraet bessmyslennuju ili loznuju informaciju

---

## 6 — profil odnogo zivotnogo i profil sredy obitaniia

**cto eto dolzno delat**

sistema dolzna razdeliat

* profil konkretnogo pitomca
* profil akvariuma
* profil terrariuma
* profil kletki
* profil voliera
* profil stada ili gruppy pri professionalnom uxode

**pocemu eto nuzno**

temperatura akvariuma ili cistka terrariuma otnositsia k srede, a ne tolko k odnoj rybe ili reptilii

**kak eto dolzno rabotat po logike**

odin pitomec moz et byt priviazan k srede obitaniia, a odna sreda moz et soderzhat neskolko pitomcev

dannye sredy ne dolzny avtomaticeski kopirovatsia kak individualnye medicinskie pokazateli kazdogo zivotnogo

**dlia kakoj celi eto delaetsia**

dlia pravilnogo razdelenija individualnogo profilia i obschej infrastruktury uxoda

**kakoj rezultat dolzen byt dostignut**

polzovatel upravliaet i konkretnym pitomcem, i ego sredoj bez dublikatov i loznyx individualnyx dannyx

---

## 7 — kto moz et sozdat profil pitomca

**cto eto dolzno delat**

profil mogut sozdaivat

* tekuscij xoziajin
* sovladelec
* priut
* proverennyj volonter
* vremennaja perederzka
* klinika pri razresenii i neobxodimosti
* celovek, nashedshij zivotnoe
* administrator organizacii
* zakonn yj predstavitel nesovershennoletnego

**pocemu eto nuzno**

ne kazdoe zivotnoe v sisteme srazu imeet podtverzhdennogo postojannogo xoziajina

**kak eto dolzno rabotat po logike**

tip sozdatelia opredeliaet nacalnyj status profilia i ego prava

naprimer profil najdennogo zivotnogo ne dolzen avtomaticeski oznachat vladenie nashedshego

**dlia kakoj celi eto delaetsia**

dlia vozmoznosti rabotat s priutskimi, poterjannymi, najdennymi i vrem enno peredannymi zivotnymi

**kakoj rezultat dolzen byt dostignut**

profil moz et byt sozdan v realnoj zivotnoj situacii, no prava vladen iia ne prisvaivajutsia bez osnovanija

---

## 8 — osnovnye tipy vladen iia profil em

**cto eto dolzno delat**

sistema dolzna razdeliat

* osnovnogo xoziajina
* sovladelca
* zakonnogo predstavitelia
* priut
* vremennuju perederzku
* sitter a
* smotritelia
* administratora profilia
* specialista s ogranichennym dostupom
* nashedshego zivotnoe
* predyduscego xoziajina

**pocemu eto nuzno**

celovek, kotoryj kormit ili vremenno soderzit pitomca, ne obyazatelno javliaetsia ego juridiceskim ili osnovnym xoziajinom

**kak eto dolzno rabotat po logike**

kazdaia rol imeet otdelnye prava, srok, istochnik i istoriju

**dlia kakoj celi eto delaetsia**

dlia tocnogo raspredeleniia otvetstvennosti

**kakoj rezultat dolzen byt dostignut**

platforma moz et otlicit postojannogo xoziajina ot sittera, priuta, nashedshego ili veterinara

---

## 9 — statusy profilia pitomca

**cto eto dolzno delat**

profil moz et imet status

* cernovik
* aktivnyj domashnij pitomec
* na perederzke
* v priute
* ishchet dom
* adopcija v processe
* peredan novomu xoziajinu
* poterialsia
* najden
* lichnost ne podtverzhdena
* sporn oe vladenie
* vrem enno skryt
* memorialnyj
* obed inen s drugim profilem
* udal iaetsia
* arhivirovan

**pocemu eto nuzno**

odin flazok aktiven ili net ne opis yvaet realnuju situaciju zivotnogo

**kak eto dolzno rabotat po logike**

status vliiaet na dostupnye dejstviia, vidimost, uvedomlenija i integracii

naprimer pri statuse poterialsia aktiviruetsia poisk, a pri memorialnom statuse otkliucajutsia bytovye napominanija

**dlia kakoj celi eto delaetsia**

dlia pravilnogo povedeniia vsej platformy na kazdom etape

**kakoj rezultat dolzen byt dostignut**

funkcii sootvetstvujut fakticeskoj situacii pitomca i ne predlagajut neumesnye dejstviia

---

# sozdanie profilia

## 10 — bystraja knopka dobavit pitomca

**cto eto dolzno delat**

knopka dolzna byt dostupna

* posle onboarding
* v profile xoziajina
* v semejnom prostranstve
* v razdele pitomcev
* pri sozdanii medkartocki
* pri registracii na sobytie
* pri poiske specialista
* pri dobavlenii gps ustrojstva

**pocemu eto nuzno**

soz d anie profilia pitomca javliaetsia odnim iz glavnyx pervyx dejstvij v socialnoj seti

**kak eto dolzno rabotat po logike**

knopka otkryvaet odin universalnyj process, a ne sozdaet raznye nesovmestimye tipy profilej v kazdom razdele

**dlia kakoj celi eto delaetsia**

dlia prostogo nacala i otsutstviia dublikatov

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et sozdat profil iz liubogo relevantnogo mesta, no rezultat vsegda odinakov i sviazan s edinym profilem

---

## 11 — minimalnyj profil za odin shag

**cto eto dolzno delat**

dlia pervogo soxraneniia dostatocno zaprosit

* imia ili vrem ennoe nazvanie
* vid
* osnovnuju fotografiju po zhelaniju
* sviaz polzovatelia s pitomcem
* bazovuju privatnost

**pocemu eto nuzno**

ogromnaia forma s desiatkami polej privodit k otkazu ili loznym zapolnenijam

**kak eto dolzno rabotat po logike**

ost alnye polia dobavljajutsia postepenno po mere ispolzovaniia konkretnyx funkcij

**dlia kakoj celi eto delaetsia**

dlia bystrogo sozdaniia rabochego profilia bez peregruzki

**kakoj rezultat dolzen byt dostignut**

profil moz et byt sozdan za korotkoe vremia i srazu ispolzovatsia dlia lenty, poisk a ili dnevnika

---

## 12 — rasshirennyj poshagovyj process

**cto eto dolzno delat**

posle minimalnogo sozdaniia sistema moz et predlozhit shagi

1. osnovnye dannye
2. fotografii
3. vozrast i pol
4. poroda ili proisxozdenie
5. vneshnie primety
6. xarakter
7. socialnye predpocteniia
8. mestopolozhenie
9. xoziajie i sovladelcy
10. privatnost
11. dokumenty i mikrochip
12. predprosmotr

**pocemu eto nuzno**

gruppirovka delaet sloznuju informaciju pon iatnoj i ne smeshivaet socialnye, medicinskie i juridiceskie dannye

**kak eto dolzno rabotat po logike**

kazdyj shag soxraniatsia otdelno, moz et byt propushchen i dostupen dlia izmeneniia pozze

**dlia kakoj celi eto delaetsia**

dlia postepennogo sozdaniia kachestvennogo profilia

**kakoj rezultat dolzen byt dostignut**

polzovatel ne ter iaetsia i ponimaet, pocemu zaprashivaetsia kazdoe pole

---

## 13 — cernovik profilia

**cto eto dolzno delat**

nezavershennyj profil dolzen soxraniatsia kak cernovik

**pocemu eto nuzno**

polzovatel moz et ne imet pod rukoj fotografii, mikrochipa, dokumentov ili tocn oj daty rozdenija

**kak eto dolzno rabotat po logike**

cernovik ne publik uetsia i dostup en tolko upolnomochennym upravliajuscim

sistema pokaz yvaet, cto esio nuzno ili polezno zapolnit

**dlia kakoj celi eto delaetsia**

dlia soxraneniia progressa bez publicacii nepolnogo profilia

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et vernutsia k profilem v liuboe vremia bez povtornogo vvoda dannyx

---

## 14 — avtomaticeskoe soxranenie

**cto eto dolzno delat**

kazhdoe izmenenie formy dolzno bezopasno soxraniatsia ili imet poniatnyj status nesoxranennogo cernovika

**pocemu eto nuzno**

mobilnyj internet moz et propast, a forma s fotografijami i opisaniem moz et zan iat mnogo vremeni

**kak eto dolzno rabotat po logike**

povtornaja otpravka ne dolzna sozdaivat vtoroj profil ili dublirujusc ie fotografii

**dlia kakoj celi eto delaetsia**

dlia ustojcivoj raboty pri slabom internete

**kakoj rezultat dolzen byt dostignut**

zakrytie prilozheniia ili vrem ennyj obryv ne privodiat k potere profilia

---

## 15 — vybor sviazi s pitomcem

**cto eto dolzno delat**

sozdatel dolzen ukazat

* ja osnovnoj xoziajin
* ja sovladelec
* ja clen semji
* ja sitter
* ja predstavitel priuta
* ja volonter
* ja nashel zivotnoe
* ja vremenno derzu zivotnoe
* ja specialist
* drugaja rol

**pocemu eto nuzno**

odin fakt sozdaniia profilia ne dolzen avtomaticeski davat polnoe vladenie

**kak eto dolzno rabotat po logike**

vybrannaia rol opredeliaet nacalnye prava i neobxodimost dopolnitelnoj proverki

**dlia kakoj celi eto delaetsia**

dlia chestnogo ustanovleniia otvetstvennosti

**kakoj rezultat dolzen byt dostignut**

nash edshij zivotnoe ne moz et vydat sebia za xoziajina, a priut moz et sozdat adopcionnyj profil bez lichnogo akkaunta odinocnogo sotrudnika

---

## 16 — proverka vozmoznyx dublikatov do publikacii

**cto eto dolzno delat**

pered sozdaniem sistema dolzna pokazat vozmoznye sovpadenija

**pocemu eto nuzno**

profil mog uze sushestvovat u drugogo sovladelca, v priute, v klinike ili v razdele najdennyx zivotnyx

**kak eto dolzno rabotat po logike**

sistema ne dolzna avtomaticeski pokazyvat cuvstitelnye dannye vozmoznogo profilia

ona moz et pokazat bezopasnuju kartocku

* fotografiju, esli publicna
* imia
* vid
* primernyj vozrast
* upravliajuscuju organizaciju
* knopku eto moj pitomec
* knopku eto drugoe zivotnoe

**dlia kakoj celi eto delaetsia**

dlia snizeniia dublikatov bez raskrytiia zakrytoj informacii

**kakoj rezultat dolzen byt dostignut**

polzovatel chasche prisojediniaetsia k sushestvujuscemu profilem vmesto sozdaniia vtorogo

---

## 17 — zapros prava na sushestvujuscij profil

**cto eto dolzno delat**

esli pitomec uze est v sisteme, polzovatel moz et otpravit zapros na

* sovladenie
* upravlenie
* vremennyj dostup
* ispravlenie sviazi
* peredacu profilia

**pocemu eto nuzno**

profil mog sozdat drugoj clen semji, priut ili predyduscij xoziajin

**kak eto dolzno rabotat po logike**

tekuscij upravliajuscij vidit, kto zaprashivaet, kak uju rol i kakie dokazatelstva predostavleny

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo prisojedineniia bez peredaci parolia

**kakoj rezultat dolzen byt dostignut**

realnyj sovladelec polucaet prava, a postoronnij ne moz et prosto zabrat profil

---

## 18 — sozdanie profilia iz adopcii

**cto eto dolzno delat**

pri zavershenii adopcii sushestvujuscij profil zivotnogo dolzen peredavatsia novomu xoziajinu

**pocemu eto nuzno**

soz d anie novogo pustogo profilia unictozit predyduscuju medicinskuju, socialnuju i adopcionnuju istoriju

**kak eto dolzno rabotat po logike**

priut vybiraet, kakie dannye peredajutsia

* osnovnaja istorija
* dokumenty
* vakcinacii
* mikrochip
* osobye potrebnosti
* publicnye fotografii
* rekomendacii po adaptacii

vnutrennie zametki priuta i dannye drugih kandidatov ne peredajutsia

**dlia kakoj celi eto delaetsia**

dlia nepreryvnosti uxoda posle adopcii

**kakoj rezultat dolzen byt dostignut**

novyj xoziajin polucaet realnuju istoriju pitomca, no ne polucaet cuzie konfidencialnye dannye

---

## 19 — sozdanie vremennogo profilia najdennogo zivotnogo

**cto eto dolzno delat**

celovek, nashedshij zivotnoe, moz et bystro sozdat vremennuju identifikacionnuju kartocku

**pocemu eto nuzno**

do ustanovleniia xoziajina zivotnomu uze nuzny fotografii, karta, chip status, klinika i koordinacija

**kak eto dolzno rabotat po logike**

profil imeet status

`najdennoe zivotnoe, xoziajin ne podtverzhden`

nashedshij ne polucaet avtomaticeskogo prava na adopciju, prodazu ili polnoe vladenie

**dlia kakoj celi eto delaetsia**

dlia organizacii bezopasnogo poiska xoziajina

**kakoj rezultat dolzen byt dostignut**

informacija o najdennom zivotnom ne ter iaetsia, no ego nelzia prisvoit prostym sozdaniem profilia

---

## 20 — profil neizvestnogo ili vremenno neopredelennogo vida

**cto eto dolzno delat**

pri srocn oj situacii sistema dolzna razresat vybrat

* vid ne opredelen
* vozmozhno koska
* vozmozhno sobaka
* ekzoticeskoe zivotnoe
* drugoe

**pocemu eto nuzno**

nash edshij moz et ne znat tocnyj vid, porodu, pol ili vozrast

**kak eto dolzno rabotat po logike**

neizvestnoe znachenie xranitsia kak neizvestno, a ne kak pustoe ili slucajno ugadannoe pole

pozze ego moz et ispravit xoziajin ili specialist

**dlia kakoj celi eto delaetsia**

dlia chestnogo sbora nepolnoj informacii

**kakoj rezultat dolzen byt dostignut**

sistema ne sozdaet loznye fakty tolko radi zapolnenija formy

---

# identifikacionnye dannye

## 21 — imia pitomca

**cto eto dolzno delat**

profil dolzen xranit tekuscee osnovnoe imia zivotnogo

**pocemu eto nuzno**

imia ispolzuetsia v profile, chat ax, sobytijax, medkartocke, dnevnik e, uvedomlenijax i poisk e

**kak eto dolzno rabotat po logike**

imia moz et soderzhat bukvy raznyx alfavitov i razumnye simvoly, no ne dolzno ispolzovatsia dlia spama, vydaci za sistemnyj akkaunt ili obmana

**dlia kakoj celi eto delaetsia**

dlia osnovnoj socialnoj i identifikacionnoj reprezentacii

**kakoj rezultat dolzen byt dostignut**

pitomec posledovatelno pokazyvaetsia pod odnim tekuscim imenem vo vsex razdelax

---

## 22 — domashnie prozvisca i alternativnye imena

**cto eto dolzno delat**

mozno dobavit

* prozvisce
* predyduscee imia
* imia iz priuta
* oficialnoe imia v dokumentax
* imia na drugom jazyke
* imia, na kotoroe reagiruet pitomec

**pocemu eto nuzno**

posle adopcii imia moz et izmenitsia, a dlia poisk a ili identifikacii staroe imia ostajotsia vaznym

**kak eto dolzno rabotat po logike**

odno imia oznachaetsia kak osnovnoe, ostalnye kak alternativnye

vidimost kazdogo mozno nastroit otdelno

**dlia kakoj celi eto delaetsia**

dlia soxraneniia istorii i podderzki raznyx realnyx scenariev

**kakoj rezultat dolzen byt dostignut**

po staromu imeni mozno najti profil, no v interfejse pokazyvaetsia aktualnoe imia

---

## 23 — izmenenie imeni

**cto eto dolzno delat**

upravliajuscij moz et izmenit publicnoe imia pitomca

**pocemu eto nuzno**

pitomca mogut pereimenovat posle adopcii, ispravit oshibku ili vybrat bolee bezopasnoe publicnoe imia

**kak eto dolzno rabotat po logike**

st aro e imia moz et ostatcia v istorii i poiskovyx sinonimax, no ne obyazatelno pokaz yvatsia publicno

**dlia kakoj celi eto delaetsia**

dlia gibkosti bez poteri identifikacii

**kakoj rezultat dolzen byt dostignut**

ssylki, druzia, dokumenty i socialnaja istoria ne teriajutsia posle pereimenovaniia

---

## 24 — vid zivotnogo

**cto eto dolzno delat**

profil dolzen imet osnovnoj biologiceskij ili prakticeskij tip

**pocemu eto nuzno**

vid opredeliaet relevantnye polia, spravocniki, rekomendacii, specialistov i pravila bezopasnosti

**kak eto dolzno rabotat po logike**

vid vybiraetsia iz upravljaemogo spravocnika, no dolzen byt variant drugoe ili neizvestno

**dlia kakoj celi eto delaetsia**

dlia pravilnoj adaptacii vsej platformy

**kakoj rezultat dolzen byt dostignut**

sobake ne predlagajutsia nastrojki akvariuma, a reptilii ne predlagajutsia obyazatelnye sobaci komandy

---

## 25 — podvid ili bolee tocnaja klassifikacija

**cto eto dolzno delat**

dlia nekotoryx kategorij mozno ukazat

* vid pticy
* vid reptilii
* vid ryby
* vid gryzuna
* vid loshadi
* biologiceskij podvid
* nauchnoe nazvanie pri neobxodimosti

**pocemu eto nuzno**

slova ptica, ryba ili reptilija slishkom obs ch ie dlia podxodiascego uxoda i poiska specialista

**kak eto dolzno rabotat po logike**

platforma dolzna xranit stabilnyj vnutrennij identifikator vida i otdelno perevody ego nazvaniia

**dlia kakoj celi eto delaetsia**

dlia tocn oj filtracii bez zavisimosti ot jazyka

**kakoj rezultat dolzen byt dostignut**

polzovatel naxodit relevantnye materialy i specialistov dlia konkretnogo vida

---

## 26 — poroda

**cto eto dolzno delat**

dlia vidov, gde poroda imeet prakticeskoe znachenie, mozno ukazat odnu ili neskolko porod

**pocemu eto nuzno**

poroda moz et pomogat v poisk e grupp, istorii proisxozdeniia, vystavkax i nekotoryx nast rojkax, no ne dolzna opredeliat ves xarakter ili zdorovje pitomca

**kak eto dolzno rabotat po logike**

varianty

* podtverzhdennaja poroda
* ukazana xoziajinom
* predpolagaemaia
* metis
* bez porody
* poroda neizvestna
* neskolko vozmoznyx porod

**dlia kakoj celi eto delaetsia**

dlia chestnogo opisaniia bez loznoj tochnosti

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et ukazat dostupnuju informaciju, a drugie vidat urov en uverennosti

---

## 27 — smeshannoe proisxozdenie

**cto eto dolzno delat**

profil dolzen podderzhivat neskolko porod ili neizvestnoe smeshannoe proisxozdenie

**pocemu eto nuzno**

mnogie pitomcy javliajutsia metisami, i zastavliat vybrat odnu porodu nepravilno

**kak eto dolzno rabotat po logike**

mozno ukazat

* izvestnye porody
* primernuju doliu po zhelaniju
* rezultat geneticeskogo testa
* predpolozhenie xoziajina
* proisxozdenie neizvestno

**dlia kakoj celi eto delaetsia**

dlia uvazitelnogo i tocnogo profilia bez diskriminacii po porode

**kakoj rezultat dolzen byt dostignut**

metis polucaet polnocennyj profil bez neobxodimosti vydavat ego za konkretnuju porodu

---

## 28 — istochnik informacii o porode

**cto eto dolzno delat**

riadom mozno ukazat

* dokument
* rodoslovnuju
* priut
* veterinara
* geneticeskij test
* predpolozhenie xoziajina
* neizvestnyj istochnik

**pocemu eto nuzno**

vneshnee poxozestvo ne vsegda pravilno opredeliaet porodu

**kak eto dolzno rabotat po logike**

poroda i istochnik xran iatsia otdelno

platforma ne dolzna avtomaticeski povysat status porody tolko po fotografii

**dlia kakoj celi eto delaetsia**

dlia pon iatnogo urovnia doverija k dannym

**kakoj rezultat dolzen byt dostignut**

drugie polzovateli ponimajut, gde fakt, a gde predpolozhenie

---

## 29 — pol pitomca

**cto eto dolzno delat**

profil moz et soderzhat

* samec
* samka
* neizvestno
* ne udalos opredelit
* drugoe podtverzhdennoe biologiceskoe sostojanie pri neobxodimosti

**pocemu eto nuzno**

pol moz et byt vazen dlia identifikacii, uxoda, medkartocki i adopcii

**kak eto dolzno rabotat po logike**

esli informacija neizvestna, eto nuzno xranit chestno, ne zastavliaja polzovatelia ugadyvat

**dlia kakoj celi eto delaetsia**

dlia korrektnoj biologiceskoj i medicinskoj informacii

**kakoj rezultat dolzen byt dostignut**

profil ne soderzit slucajno pridumannogo znacheniia

---

## 30 — reproduktivnyj status

**cto eto dolzno delat**

mozno ukazat

* ne sterilizovan
* sterilizovan
* kastrirovan
* status neizvesten
* procedura zaplanirovana
* est medicinskoe iskliuchenie
* ne otnositsia k dannomu vidu

**pocemu eto nuzno**

eta informacija moz et byt vazna dlia medkartocki, adopcii, povedeniia i nekotoryx sobytij

**kak eto dolzno rabotat po logike**

publicnaja vidimost nast r aivaetsia otdelno, a professionalnoe podtverzhdenie xranitsia v medkartocke

**dlia kakoj celi eto delaetsia**

dlia razdelenija socialnoj metki i podtverzhdennogo medicinskogo fakta

**kakoj rezultat dolzen byt dostignut**

publicnyj profil moz et pokazat prostoj status, ne raskryvaja dokumenty i medicinskie podrobnosti

---

## 31 — data rozdenija

**cto eto dolzno delat**

mozno ukazat

* tocn uju datu
* primernuju datu
* tolko mesiac i god
* tolko god
* primernyj vozrast
* data neizvestna
* uslovnyj den rozdenija

**pocemu eto nuzno**

u priutskix i najdennyx zivotnyx tocn aja data c asto neizvestna

**kak eto dolzno rabotat po logike**

sistema dolzna xranit ne tolko datu, no i urov en tochnosti

**dlia kakoj celi eto delaetsia**

dlia chestnogo rascheta vozrasta bez loznoj tochnosti

**kakoj rezultat dolzen byt dostignut**

profil moz et pokazat primerno tri goda, ne vydavaja uslovnuju datu za dokazannyj fakt

---

## 32 — avtomaticeskij raschet vozrasta

**cto eto dolzno delat**

vozrast dolzen avtomaticeski obnovliatsia na osnove dostupnoj daty ili ocenki

**pocemu eto nuzno**

rucnoe pole vozrast bystro ustarevaet

**kak eto dolzno rabotat po logike**

esli data tocn aja, pokaz yvaetsia tocnyj vozrast

esli data primernaja, pokaz yvaetsia primernyj vozrast ili diapazon

**dlia kakoj celi eto delaetsia**

dlia aktualnogo otobrazheniia bez postojannogo redaktirovanija

**kakoj rezultat dolzen byt dostignut**

profil ne pokazyvaet pitomca kak dvuxletnego cerez piat let posle sozdaniia

---

## 33 — zivotn yj etap

**cto eto dolzno delat**

profil moz et pokazat etap

* novorozdennyj
* malenjkij
* molodoj
* vzroslyj
* pozhiloj
* etap neizvesten
* individualnaja kategorija dlia vida

**pocemu eto nuzno**

vozrastnye etapy razny dlia sobaki, popugaja, loshadi i reptilii

**kak eto dolzno rabotat po logike**

etap opredeliaetsia po spravocniku vida, no xoziajin ili specialist moz et ego utochnit

**dlia kakoj celi eto delaetsia**

dlia podxodiascix rekomendacij i filtracii

**kakoj rezultat dolzen byt dostignut**

sistema ne primeniaet sobac i e vozrastnye granicy ko vsem vidam

---

## 34 — okras

**cto eto dolzno delat**

profil dolzen podderzhivat strukturirovannoe opisanie okrasa

* osnovnoj cvet
* dopolnitelnye cveta
* pjatna
* polosy
* gradient
* osobennosti perjev
* osobennosti cesui
* sezonnoe izmenenie

**pocemu eto nuzno**

okras vazen dlia identifikacii, poisk a poterjannogo pitomca i socialnogo profilia

**kak eto dolzno rabotat po logike**

mozno vybrat cveta iz spravocnika i dobavit tekstovoe utochnenie

**dlia kakoj celi eto delaetsia**

dlia poiska i tocnogo opisaniia vnesnosti

**kakoj rezultat dolzen byt dostignut**

pri propazhe avtomaticeski formiruetsia poleznoe opisanie bez neobxodimosti pisat ego s nulia

---

## 35 — tip shersti, perjev, cesui ili kozhi

**cto eto dolzno delat**

v zavisimosti ot vida mozno ukazat

* dlinnu shersti
* teksturu
* podsh erstok
* otsutstvie shersti
* tip perjev
* okras cesui
* sostojanie kozhi
* tip grivy
* sezonn uju lin ku

**pocemu eto nuzno**

raznym vidam nuzny raznye opisatel nye polia

**kak eto dolzno rabotat po logike**

forma meniaetsia po vidu, a obschie vnutrennie kategorii pozvoliajut poisk i rekomendacii

**dlia kakoj celi eto delaetsia**

dlia individualnogo opisaniia i podxodiascego uxoda

**kakoj rezultat dolzen byt dostignut**

grumer, priut ili nashedshij zivotnoe polucaet pon iatnoe i relevantnoe opisanie

---

## 36 — osobye vneshnie primety

**cto eto dolzno delat**

mozno ukazat

* shram
* pjatno
* osobennost uha
* raznyj cvet glaz
* otsutstvujuscuju cast x vosta
* osobennost lapy
* tatu i rovku
* neobycn yj risunok
* deformaciju
* posledstvie staroj travmy

**pocemu eto nuzno**

osobye primety pomogajut podtverzhdat lichnost pitomca pri propazhe, adopcii ili spore

**kak eto dolzno rabotat po logike**

kazdaia primeta moz et byt

* publicnoj
* dostupnoj tolko druzjam
* skrytoj proverocnoj
* dostupnoj klinike
* dostupnoj pri poisk e

**dlia kakoj celi eto delaetsia**

dlia identifikacii bez raskrytiia vseh proverocnyx detalej

**kakoj rezultat dolzen byt dostignut**

u xoziajina est skrytye dokazatelstva, kotorye moshennik ne moz et prosto skopirovat iz publicnogo profilia

---

## 37 — razmer pitomca

**cto eto dolzno delat**

mozno ukazat

* ocen malenjkij
* malenjkij
* srednij
* krupnyj
* ocen krupnyj
* individualnye razmery
* ne otnositsia k vidu

**pocemu eto nuzno**

razmer vazen dlia transporta, uslug, marketplace, sobytij i poisk a

**kak eto dolzno rabotat po logike**

obschaja kategorija ne zameniaet fakticeskie zamery, no pomogaet bystroj filtracii

**dlia kakoj celi eto delaetsia**

dlia bazovoj sovmestimosti s mestami, tovarami i uslugami

**kakoj rezultat dolzen byt dostignut**

sistema moz et otfiltrovat slishkom malenkuju perenosku ili uslugu, ne ispolzuja cuvstitelnye medicinskie dannye

---

## 38 — fakticeskie zamery

**cto eto dolzno delat**

mozno xranit

* ves
* rost
* dlinu tela
* obxvat grudi
* obxvat shei
* dlinu spiny
* dlinu mordy
* razmax krylev
* dlinu panciria
* drugie vidovye izmerenija

**pocemu eto nuzno**

tovary i uslugi c asto trebu jut tocn yx razmerov

**kak eto dolzno rabotat po logike**

publicnyj profil moz et pokaz yvat poslednie odobrennye zamery, a polnaja dinamika xranitsia v dnevnik e ili medkartocke

**dlia kakoj celi eto delaetsia**

dlia prakticeskogo podbora bez smeshivaniia socialnoj kartocki s medicinskoj istoriej

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et bystro vybrat shlejku, perenosku ili mesto bez povtornogo izmerenija

---

## 39 — publicnyj ves i medicinskij ves

**cto eto dolzno delat**

sistema dolzna razdeliat

* primernyj publicnyj ves
* poslednee domashnee izmerenie
* podtverzhdennyj kliniceskij ves
* polnuju dinamiku v medkartocke

**pocemu eto nuzno**

ves moz et byt i prakticeskim parametrom, i cuvstitelnym medicinskim pokazatelem

**kak eto dolzno rabotat po logike**

xoziajin sam reshaet, pokaz yvat li ves publicno

dannye iz kliniki ne publikujutsia avtomaticeski

**dlia kakoj celi eto delaetsia**

dlia razdelenija socialnoj polzy i medicinskoj privatnosti

**kakoj rezultat dolzen byt dostignut**

marketplace moz et proverit sovmestimost perenoski, ne otkryvaja polnuju istoriju izmeneniia vesa

---

## 40 — cvet glaz

**cto eto dolzno delat**

mozno ukazat odin ili raznye cveta glaz

**pocemu eto nuzno**

eto moz et byt vaznoj osoboj primetoj i castiu socialnogo profilia

**kak eto dolzno rabotat po logike**

dlia dvux glaz mozno ukazat raznye znacheniia, odin glaz moz et byt otmechen kak otsutstvuet ili imeet medicinskuju osobennost bez publicacii diagnoza

**dlia kakoj celi eto delaetsia**

dlia tocn oj vneshnej identifikacii

**kakoj rezultat dolzen byt dostignut**

opisanie ne obobsch a et neobycnuju osobennost odn im polem

---

## 41 — identifikacionnaja fotografija

**cto eto dolzno delat**

profil dolzen imet otdelnuju aktualnuju fotografiju dlia identifikacii

**pocemu eto nuzno**

krasivaja stilizovannaja fotografija ne vsegda podxodit dlia poisk a ili kliniki

**kak eto dolzno rabotat po logike**

identifikacionnaja fotografija moz et byt otdelena ot publicnogo avatara i pokaz yvat

* mor du
* okras
* telo
* osobye primety
* tekuscuju strizhku

**dlia kakoj celi eto delaetsia**

dlia poisk a, dokumentov i proverki lichnosti pitomca

**kakoj rezultat dolzen byt dostignut**

pri propazhe sistema ispolzuet aktualnoe realisticeskoe izobrazhenie, a ne star yj filtr ovannyj avatar

---

# fotografii i media

## 42 — glavn yj avatar pitomca

**cto eto dolzno delat**

avatar dolzen predstavliat pitomca vo vsej socialnoj seti

**pocemu eto nuzno**

on ispolzuetsia v lente, kommentariiax, druziax, sobytijax, chat ax i kartockax

**kak eto dolzno rabotat po logike**

xoziajin vybiraet odnu fotografiju, obrezaet ejo i vidit predprosmotr v raznyx razmerax

**dlia kakoj celi eto delaetsia**

dlia uznavaemosti profilia

**kakoj rezultat dolzen byt dostignut**

avatar xoro sho vygl iadit i v bolshoj kartocke, i v malenkoj ikonke

---

## 43 — oblozhka profilia pitomca

**cto eto dolzno delat**

oblozhka moz et pokaz yvat pitomca, ego sred u, interesy ili krasivoe nejtralnoe izobrazhenie

**pocemu eto nuzno**

ona sozdaet individualn yj stil profilia

**kak eto dolzno rabotat po logike**

pered publicaciej nuzno preduprezhdat o

* domashnem adrese
* nomere avtomobilia
* dokumentax
* licax detej
* qr kodax
* tocn oj lokacii
* nomere telefona

**dlia kakoj celi eto delaetsia**

dlia personalizacii bez slucajnoj utecki dannyx

**kakoj rezultat dolzen byt dostignut**

profil vygl iadit individualno, no oblozhka ne raskryvaet dom i raspisanie semji

---

## 44 — galereja pitomca

**cto eto dolzno delat**

profil moz et imet strukturirovannuju galereju

* fotografii
* video
* albomy
* vaznye sobytija
* fotografii do i posle
* dokumentalnye fotografii
* fotografii iz priuta
* professionalnye fotosessii

**pocemu eto nuzno**

odin beskonechnyj spisok media bystro stanovitcia neudobnym

**kak eto dolzno rabotat po logike**

kazdyj mediafajl imeet

* avtora
* datu
* privatnost
* opisanie
* sviazannoe sobytie
* status soglasiia
* alternativnyj tekst
* istoriju redaktirovanija

**dlia kakoj celi eto delaetsia**

dlia organizovannoj vizualn oj istorii

**kakoj rezultat dolzen byt dostignut**

xoziajin bystro naxodit fotografii po godu, sobytiju, mestu ili etapu zizni

---

## 45 — fotografii po vremeni

**cto eto dolzno delat**

media mozno pokaz yvat po etapam

* malenjkij vozrast
* pervyj dom
* adopcija
* pervaja progulka
* puteshestvija
* dostizeniia
* pozhiloj vozrast
* memorialnaja istoria

**pocemu eto nuzno**

profil pitomca javliaetsia dolgosrocnoj cifrovoj istoriej, a ne tolko tekuscej kartockoj

**kak eto dolzno rabotat po logike**

fotografii priviazyvajutsia k date i po zhelaniju k etapu

esli data neizvestna, eto ukazyvaetsia chestno

**dlia kakoj celi eto delaetsia**

dlia vizualn oj xronologii zizni

**kakoj rezultat dolzen byt dostignut**

profil moz et pokazat rost i istoriju pitomca bez rucnogo sozdaniia otdelnyx postov

---

## 46 — aktualnost fotografii

**cto eto dolzno delat**

sistema moz et napomnit obnovit identifikacionnuju fotografiju, esli ona staraia

**pocemu eto nuzno**

strizhka, okras, razmer i vozrast mogut silno izmenit vneshnost

**kak eto dolzno rabotat po logike**

napominanie ne dolzno zastavliat publicovat novuju fotografiju

ona moz et byt soxranena tolko dlia poisk a i dokumentov

**dlia kakoj celi eto delaetsia**

dlia aktualnoj identifikacii

**kakoj rezultat dolzen byt dostignut**

pri propazhe ne ispolzuetsia fotografija pitomca piatiletnej davnosti, na kotoroj on vygl iadit sovsem inache

---

## 47 — soglasie avtora fotografii

**cto eto dolzno delat**

pri zagruzke professionalnoj ili cuzoj fotografii mozno ukazat

* avtora
* pravo publikacii
* licenziju
* ssylku na fotografa
* ogranichenie ispolzovaniia
* zapret kommerceskogo ispolzovaniia

**pocemu eto nuzno**

fotografija pitomca moz et prinadlezhat fotografu ili organizacii, a ne xoziajinu

**kak eto dolzno rabotat po logike**

platforma dolzna podderzhivat zhalobu na narushenie avtorskix prav i zamen u fajla bez udaleniia vsego profilia

**dlia kakoj celi eto delaetsia**

dlia zakonnoj i uvazitelnoj publikacii

**kakoj rezultat dolzen byt dostignut**

profil ne stanovitsia mestom massovogo kopirovaniia cuzix fotografij

---

## 48 — alternativnyj tekst fotografii

**cto eto dolzno delat**

k vaznoj fotografii mozno dobavit opisanie

naprimer

`baks, krupnaja cernaja sobaka s belym pjatnom na grudi, sidit v sneznom parke`

**pocemu eto nuzno**

eto pomogaet polzovateliam s narushenijami zreniia i ulucshaet pon iatnost media

**kak eto dolzno rabotat po logike**

sistema moz et predlozhit avtomaticeskij cernovik, no xoziajin dolzen moc ego ispravit

**dlia kakoj celi eto delaetsia**

dlia dostupnosti i tocnogo opisaniia

**kakoj rezultat dolzen byt dostignut**

vaznoe izobrazhenie pon iatno bez obyazatelnogo vizualnogo prosmotra

---

## 49 — avtomaticeskoe udalenie metadannyx fotografii

**cto eto dolzno delat**

pered publicaciej sistema dolzna po umolcaniju udal iat lishnie metadannye

* gps koordinaty
* model ustrojstva
* skrytye kommentarii
* vnutrennie identifikatory
* tocn oe vremia, esli ono ne nuzno

**pocemu eto nuzno**

fotografija doma moz et nezametno raskryt tocn yj adres

**kak eto dolzno rabotat po logike**

original moz et xranitsia zakryto po resheniju xoziajina, no publicnaja kopija dolzna byt ocischena

**dlia kakoj celi eto delaetsia**

dlia zascity lokacii i lichnyx dannyx

**kakoj rezultat dolzen byt dostignut**

publicnaja fotografija pitomca ne raskryvaet domashnjuju gps tochku

---

## 50 — zamazyvanie lichnyx dannyx na media

**cto eto dolzno delat**

redaktor dolzen pozvoliat skryt

* lic a liudej
* lic a detej
* nomer avtomobilia
* adres
* telefon
* dokument
* nomer mikrochipa
* qr kod
* ekran kompiutera
* identifikacionnyj braslet

**pocemu eto nuzno**

xoziajin moz et ne zametit cuvstitelnuju detal na zadnem plane

**kak eto dolzno rabotat po logike**

redaktor soxr aniaet zakrytyj original i publicnuju obrabotannuju kopiju, esli xoziajin xocet

**dlia kakoj celi eto delaetsia**

dlia bezopasnoj publicacii bez poteri originala

**kakoj rezultat dolzen byt dostignut**

profil ostajotsia krasivym, no ne raskryvaet dannye semji i dokumentov

---

## 51 — moderacija media

**cto eto dolzno delat**

fotografii i video dolzny proveriatsia na

* zhestokoe obrashenie
* seksualizirovannyj kontent
* dokumenty s lichnymi dannymi
* ukradennye media
* poddeln yj kontent
* nelegalnye vidy
* opasnye dejstviia
* reklamn yj spam
* graficeskie medicinskie izobrazheniia

**pocemu eto nuzno**

profil pitomca moz et ispolzovatsia dlia rasprostraneniia vreda ili moshennicestva

**kak eto dolzno rabotat po logike**

cuvstitelnoe, no dopustimoe medicinskoe media moz et byt skryto za preduprezhdeniem

opasnoe ili zapreshchenn oe udal iaetsia ili otpravliaetsia na proverku

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo socialnogo prostranstva

**kakoj rezultat dolzen byt dostignut**

poleznye dokumentalnye media soxraniajutsia, no zhestokij ili nelegalnyj kontent ne prodvigaetsia

---

# publicnoe opisanie pitomca

## 52 — kratkoe bio pitomca

**cto eto dolzno delat**

xoziajin moz et napisat korotkoe predstavlenie

* kakoi pitomec po xarakteru
* cto liubit
* cego boitsia
* kak provodit vremia
* ishchet li druzej
* ucavstvuet li v sobytijax

**pocemu eto nuzno**

nabor texniceskix polej ne sozdaet socialnogo obraza

**kak eto dolzno rabotat po logike**

bio dolzno prohodit proverku na lichnye kontakty, opasnye sovety, reklamu i vydacu za professionalnoe zakliuchenie

**dlia kakoj celi eto delaetsia**

dlia pon iatnogo i zivogo socialnogo predstavleniia

**kakoj rezultat dolzen byt dostignut**

drugoj xoziajin ponimaet bazovyj xarakter i interesy pitomca do kontakta

---

## 53 — opisanie ot lica pitomca

**cto eto dolzno delat**

platforma moz et razresit tvorceskoe opisanie ot lica pitomca

naprimer

`ja baks, liubliu medlennye progulki i ne liubliu gromkie zvuki`

**pocemu eto nuzno**

takoi stil popular en v socialnyx setiax i delaet profil emocionalnym

**kak eto dolzno rabotat po logike**

interfejs dolzen jasno pokaz yvat, cto profilem upravliaet celovek

tvorceskij stil ne dolzen skryvat realnogo otvetstvennogo upravliajuscego pri sdelkax, zhalobax ili professionalnyx voprosax

**dlia kakoj celi eto delaetsia**

dlia emocionalnogo socialnogo opyta bez poteri otvetstvennosti

**kakoj rezultat dolzen byt dostignut**

profil moz et byt napisan veselo, no drugie ne putajut pitomca s samostojatelnym juridiceskim polzovatelem

---

## 54 — xarakter

**cto eto dolzno delat**

mozno vybrat opisatel nye metki

* spokojnyj
* aktivnyj
* ostoroznyj
* druzeliubnyj
* samostojatelnyj
* igrivyj
* trevoznyj
* liubopytnyj
* medlennyj
* socialnyj
* nuzdaetsia v vremeni dlia privykaniia

**pocemu eto nuzno**

xarakter vazen dlia znakomstv, sobytij, perederzki i vybora uslug

**kak eto dolzno rabotat po logike**

metki dolzny byt nabludeniem xoziajina, a ne professionalnym diagnozom

kazdaia moz et imet kontekst

naprimer spokojnyj doma, no trevoznyj v transporte

**dlia kakoj celi eto delaetsia**

dlia bolee tocnogo ponimaniia povedeniia

**kakoj rezultat dolzen byt dostignut**

pitomec ne polucaet odnu uproshchenn uju metku, kotoraja ne ucit yvaet situaciju

---

## 55 — uroven aktivnosti

**cto eto dolzno delat**

mozno ukazat

* ocen nizkij
* nizkij
* srednij
* vysokij
* ocen vysokij
* meniaetsia po situacii
* neizvestno
* ogranichen specialistom

**pocemu eto nuzno**

uroven aktivnosti vazen dlia druzej, progulok, sobytij i sittera

**kak eto dolzno rabotat po logike**

eto socialnoe nabludenie ne dolzno avtomaticeski schitatsia medicinskim pokazatelem ili normoj

**dlia kakoj celi eto delaetsia**

dlia podbora podxodiascego formata vzaimodejstviia

**kakoj rezultat dolzen byt dostignut**

pozhilomu medlennomu pitomcu ne rekomendujutsia tolko intensivnye gruppovye prob ezki

---

## 56 — liubimye aktivnosti

**cto eto dolzno delat**

profil moz et pokaz yvat

* spokojnuju progulku
* beg
* plavanie
* igru s miacom
* poiskovye igry
* lazan ie
* trening
* fotografirovanie
* poezdki
* son
* nabludenie iz okna
* kupanie
* obogaschenie sredy

**pocemu eto nuzno**

obschie interesy pomogajut naxodit druzej i sobytija

**kak eto dolzno rabotat po logike**

spisok zavisit ot vida, no moz et rasshiri atsia polzovatelem

**dlia kakoj celi eto delaetsia**

dlia socialnoj sovmestimosti po realnym predpoctenijam

**kakoj rezultat dolzen byt dostignut**

rekomenduemye sobytija i znakomstva luchshe sootvetstvujut pitomcu

---

## 57 — cto pitomec ne liubit

**cto eto dolzno delat**

mozno dobrovolno ukazat

* gromkie zvuki
* tolpu
* prikosnovenie k opredelennoj zone
* neznakomyx
* transport
* lift
* vodu
* fen
* drugih zivotnyx
* detej
* opredelennye tipy igr

**pocemu eto nuzno**

eta informacija povysaet bezopasnost pri progulkax, gruminge, sobytijax i perederzke

**kak eto dolzno rabotat po logike**

tekst dolzen byt konkretnym i ne ispolzovat moralnye ocenki

naprimer ne liubit prikosnovenie k zadnej lape luchshe, chem ploxoj i agressivnyj

**dlia kakoj celi eto delaetsia**

dlia predotvrashcheniia stressa i incidentov

**kakoj rezultat dolzen byt dostignut**

drugoj xoziajin ili specialist znaet, cego izbegat pri pervom kontakte

---

## 58 — straxi i triggeri

**cto eto dolzno delat**

profil moz et soderzhat

* fejerverk
* grozu
* mashiny
* samokaty
* velosipedy
* gromkie golosa
* neznakom yx liudej
* veterinar nuju kliniku
* perenosku
* drugix zivotnyx
* opredelennye dvizheniia
* ostavanie odnomu

**pocemu eto nuzno**

trigger moz et silno izmenit povedenie pitomca i sozdat risk pobega ili zascitnoj reakcii

**kak eto dolzno rabotat po logike**

vidimost triggerov mozno ogranichit

naprimer oni dostupny druzjam, sitteru i specialistam, no ne obyazatelno vsem posetit eliam profilia

**dlia kakoj celi eto delaetsia**

dlia bezopasnoj organizacii kontakta i uxoda

**kakoj rezultat dolzen byt dostignut**

pitomec ne popadaet v situaciju, kotoruju mozno bylo predotvratit prostym znaniem konteksta

---

## 59 — bezopasnyj sposob podojti k pitomcu

**cto eto dolzno delat**

xoziajin moz et ukazat instrukciju

* podojti bokom
* ne smotret priamo
* ne nakloniatsia sverxu
* dat vremia obniuhat
* ne trogat bez razresheniia
* ne zvat gromko
* ne pytatsia pojmat
* snachala sviazatsia s xoziajinom
* ne podxodit bez specialista

**pocemu eto nuzno**

odna i ta ze popytka kontakta moz et byt prijatnoj dlia odnogo pitomca i strashnoj dlia drugogo

**kak eto dolzno rabotat po logike**

instrukcija moz et avtomaticeski pokaz yvatsia

* v objavlenii o propazhe
* na sobytii
* sitteru
* grumeru
* veterinaru
* novomu drugu

**dlia kakoj celi eto delaetsia**

dlia snizeniia riskov pri pervom kontakte

**kakoj rezultat dolzen byt dostignut**

liudi znaiut, kak dejstvovat, ne prov ocir uja pobeg, paniku ili zascitnuju reakciju

---

## 60 — komandy i signaly, na kotorye reagiruet pitomec

**cto eto dolzno delat**

mozno ukazat

* imia
* komandy
* zvuk
* svist
* signal rukoj
* slova na neskolkix jazykax
* zvuk kormushki
* igrushku

**pocemu eto nuzno**

eta informacija polezna dlia sittera, kinologa, priuta i poisk a poterjannogo pitomca

**kak eto dolzno rabotat po logike**

publicno mozno pokaz yvat tolko bezopasnye obschie komandy

nekotorye proverocnye signaly mozno skryt

**dlia kakoj celi eto delaetsia**

dlia upravljaemogo kontakta i identifikacii

**kakoj rezultat dolzen byt dostignut**

upolnomochennyj celovek moz et pravilno obratitsia k pitomcu bez poluceniia vseh konfidencialnyx detalej

---

## 61 — predpoctitelnyj uroven kontakta

**cto eto dolzno delat**

mozno ukazat

* liubit prikosnoveniia
* predpochitaet sam podojti
* ne liubit, kogda ego podnimajut
* razreshaet gl adit tolko xoziajinu
* nuzno vremia na privykanie
* kontakt zavisit ot situacii
* neizvestno

**pocemu eto nuzno**

profil dolzen pomogat uvazat granicy pitomca

**kak eto dolzno rabotat po logike**

metka ne dolzna byt obyazatelnoj i ne dolzna vydavat subjektivnoe nabludenie za postojannyj fakt

**dlia kakoj celi eto delaetsia**

dlia ulucsheniia blagopoluchiia pri socialnom vzaimodejstvii

**kakoj rezultat dolzen byt dostignut**

drugie liudi ne prinuzdajut pitomca k kontaktu tolko potomu, cto on vygl iadit druzeliubnym

---

## 62 — otnoshenie k detiam

**cto eto dolzno delat**

mozno ukazat

* est polozitelnyj opyt
* predpochitaet spokojnyx detej
* nuzhen kontrol vzroslogo
* net opyta
* ne rekomenduetsia kontakt
* neizvestno

**pocemu eto nuzno**

slovo liubit detej moz et sozdat loznuju garantiju bezopasnosti

**kak eto dolzno rabotat po logike**

sistema dolzna izbegat absoliutnyx formulirovok i napominat o kontrole vzroslogo

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo plan iro vaniia vstrech i adopcii

**kakoj rezultat dolzen byt dostignut**

polzovatel ne schitaet odnu metku garantiej, no polucaet poleznyj kontekst

---

## 63 — otnoshenie k drugim sobakam

**cto eto dolzno delat**

mozno ukazat

* druzeliuben
* predpochitaet parallel n uju progulku
* liubit spokojnyx sobak
* liubit aktivnye igry
* izbeg a et kontakta
* trebuet distanciju
* est reakcia na opredelennye tipy
* net dostatocno opyta
* kontakt ne rekomenduetsia bez specialista

**pocemu eto nuzno**

dlia druzhby pitomcev i gruppovyx progulok nuzhen bolee tocn yj kontekst, chem prosto da ili net

**kak eto dolzno rabotat po logike**

metka moz et meniatcia po rezultatam novogo opyta, no istorija vaznyx izmenenij dolzna soxraniatsia zakryto

**dlia kakoj celi eto delaetsia**

dlia podbora bolee bezopasnyx znakomstv

**kakoj rezultat dolzen byt dostignut**

sistema ne rekomenduet slucajn uju tesnuju igru dvum pitomcam, kotorym nuzna distancija

---

## 64 — otnoshenie k koskam i drugim vidam

**cto eto dolzno delat**

dlia kazdogo relevantnogo vida mozno ukazat

* spokojno
* proiavliaet interes
* izbeg a et
* presleduet
* net opyta
* nuzhen kontrol
* kontakt ne rekomenduetsia
* neizvestno

**pocemu eto nuzno**

obschaja metka druzeliuben ne opis yvaet mezhvidovoe povedenie

**kak eto dolzno rabotat po logike**

profil dolzen xranit otnoshenie po kategorijam, a ne odin universalnyj flazok

**dlia kakoj celi eto delaetsia**

dlia adopcii, perederzki, grupp i domashnego znakomstva

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et ocenit bazovyj risk sovmestnogo prozhivaniia do pervoj vstrechi

---

## 65 — neizvestno ne ravnjaetsia ploxo

**cto eto dolzno delat**

v pov edenceskix poliax dolzen byt variant

`net dostatocno opyta`

**pocemu eto nuzno**

otsutstvie podtverzhdennogo polozitelnogo opyta ne oznachaet agressiju ili nesovmestimost

**kak eto dolzno rabotat po logike**

sistema dolzna razdeliat

* izvestn yj polozitelnyj opyt
* izvestn yj sloznyj opyt
* net opyta
* informacija protivorechiva
* neizvestno

**dlia kakoj celi eto delaetsia**

dlia chestnogo i neosuzhdajuscego profilia

**kakoj rezultat dolzen byt dostignut**

pitomec ne polucaet negativnuju reputaciju tolko iz-za otsutstviia dannyx

---

## 66 — povedenceskie metki ne dolzny byt kley mom

**cto eto dolzno delat**

profil dolzen izbegat prostyx publicnyx metok

* opasnyj
* zloj
* ploxoj
* problemnyj
* neobu chaemyj

**pocemu eto nuzno**

takie slova ne objasniajut kontekst i mogut navsegda povredit adopcii ili otnosheniju k pitomcu

**kak eto dolzno rabotat po logike**

nuzno opis yvat konkretnoe povedenie i situaciju

naprimer

* rycit pri prikosnovenii k bolnoj lape
* pyt a etsia ub ezat pri gromkom zvuke
* zasciscaet edu ot drugih sobak

**dlia kakoj celi eto delaetsia**

dlia tocnogo i spravedlivogo opisaniia

**kakoj rezultat dolzen byt dostignut**

drugie ponimajut realnyj risk i sposob upravleniia bez moralnoj ocenki pitomca

---

## 67 — bazovaia liniia oby cnogo povedeniia

**cto eto dolzno delat**

xoziajin moz et opisat, kak pitomec oby cno

* est
* p iot
* spal
* igraet
* obsch a etsia
* reagiruet na zvuki
* perenosit odinochestvo
* vedet sebia na progulke
* reagiruet na prikosnovenie

**pocemu eto nuzno**

bez bazovoj linii trudno zametit znachim oe izmenenie

**kak eto dolzno rabotat po logike**

eto opisanie moz et ispolzovatsia v dnevnik e i medkartocke, no ne obyazatelno publikovatsia

**dlia kakoj celi eto delaetsia**

dlia rannego obnaruzheniia izmenenij v rutin e i povedenii

**kakoj rezultat dolzen byt dostignut**

xoziajin i specialist mogut sravnit tekuscee sostojanie s oby cnym, a ne s abstraktnoj normoj

---

# socialnyj profil i publicnaja kartocka

## 68 — osnovnaja publicnaja kartocka

**cto eto dolzno delat**

v katalogax i rekomendacijax kartocka moz et pokaz yvat

* avatar
* imia
* vid
* porodu ili metis
* vozrastnuju gruppu
* gorod ili region
* osnovnye interesy
* socialnyj status
* upravliajuscego xoziajina v dopustimom vide
* znachki
* knopku otkryt profil

**pocemu eto nuzno**

polzovatel dolzen ponimat osnovnoj kontekst do otkrytija polnogo profilia

**kak eto dolzno rabotat po logike**

kartocka ne dolzna pokaz yvat skrytye medicinskie dannye, tocn uju lokaciju, telefon ili dokumenty

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo poiska i obzora

**kakoj rezultat dolzen byt dostignut**

profil mozno ocenit po bazovym socialnym parametram bez utecki cuvstitelnoj informacii

---

## 69 — glavnyj ekran profilia

**cto eto dolzno delat**

na profile mozno pokazat

* oblozhku
* avatar
* imia
* vozrast
* vid
* porodu
* kratkoe bio
* xoziaev
* socialnyj status
* interesy
* druzej
* publikacii
* sobytija
* albomy
* znachki
* bezopasnyj sposob kontakta

**pocemu eto nuzno**

vaznaja informacija ne dolzna byt razbrosana po desiatkam nesviazannyx ekranov

**kak eto dolzno rabotat po logike**

blok i pokazyvajutsia v zavisimosti ot prav zritel ia

**dlia kakoj celi eto delaetsia**

dlia edinogo pon iatnogo socialnogo predstavleniia

**kakoj rezultat dolzen byt dostignut**

drugoj polzovatel za odin prosmotr ponimaet osnovnoe o pitomce i dostupnye dejstviia

---

## 70 — vkladki profilia

**cto eto dolzno delat**

profil moz et imet vkladki

* obzor
* publikacii
* media
* druzia
* gruppy
* sobytija
* mesta
* dostizeniia
* istoria
* adopcija
* publicnye dokumenty
* memorial

**pocemu eto nuzno**

po mere razvitiia profil moz et soderzhat mnogo raznogo kontenta

**kak eto dolzno rabotat po logike**

vkladka pokaz yvaetsia tolko pri nalichii dannyx i dostup a

pustye ili zakrytye razdely ne dolzny raskryvat, cto v nix est skrytaja informacija

**dlia kakoj celi eto delaetsia**

dlia strukturirovannogo prosmotra

**kakoj rezultat dolzen byt dostignut**

profil ostajotsia udobnym i pri piati fotografijax, i pri mnogoletnej istorii

---

## 71 — stabilnaia ssylka profilia

**cto eto dolzno delat**

u pitomca dolzna byt stabilnaia publicnaja ssylka ili slug

**pocemu eto nuzno**

ssylka moz et byt opublikovana na adresnike, qr kode, v socsetiax, na plakat e ili v dokumentax

**kak eto dolzno rabotat po logike**

izmenenie imeni pitomca ne dolzno lomat star y e ssylki

mozno ispolzovat stabilnyj identifikator i meniaemyj cit aemyj slug

**dlia kakoj celi eto delaetsia**

dlia dolgosrocnogo dostup a

**kakoj rezultat dolzen byt dostignut**

staryj qr kod prodolzaet otkryvat pravilnyj profil posle pereimenovaniia

---

## 72 — qr kod profilia

**cto eto dolzno delat**

profil moz et sozdat qr kod dlia

* adresnika
* perenoski
* dokumentov
* vizitki
* poisk a pri propazhe
* kliniki
* sittera
* sobytija

**pocemu eto nuzno**

qr kod pozvoliaet bystro otkryt aktualnuju informaciju bez publicacii polnogo telefona

**kak eto dolzno rabotat po logike**

xoziajin vybiraet, cto vidit gost po qr

* publicnyj profil
* ekstrennuju kartocku
* kontakt cerez platformu
* status poterialsia
* minimalnye instrukcii

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo fiziceskogo sviazyvaniia pitomca s cifrovym profilem

**kakoj rezultat dolzen byt dostignut**

nashedshij moz et sviazatsia s xoziajinom, ne poluchaja avtomaticeski ego lichnyj nomer ili adres

---

## 73 — vremennyj qr kod

**cto eto dolzno delat**

mozno sozdat qr s ogranichennym srokom dlia

* sittera
* kliniki
* perederzki
* poezdki
* sobytija
* adopcionnoj vstrechi

**pocemu eto nuzno**

postojannyj publicnyj qr ne vsegda nuzhen

**kak eto dolzno rabotat po logike**

kod imeet srok, prava i moz et byt otzyvan do istecheniia

**dlia kakoj celi eto delaetsia**

dlia vremennogo dostup a bez peredaci polnogo profilia

**kakoj rezultat dolzen byt dostignut**

posle zaversheniia uslugi ili poezdki staryj kod bolshe ne otkryvaet dannye

---

## 74 — kontakt s xoziajinom cerez profil

**cto eto dolzno delat**

posetitel moz et

* otpravit zapros na dialog
* soobscit o nabludenii
* otpravit fotografiju
* predlozhit druzhbu
* zadat vopros
* pozhalovatsia
* sviazatsia po adopcii
* sviazatsia po sobytiju

**pocemu eto nuzno**

publicnyj telefon ili email ne dolzen byt obyazatelnym dlia kontakta

**kak eto dolzno rabotat po logike**

soobsenie peredajotsia cerez platformu s zascitoj ot spama, blokirovkoj i zhaloboj

**dlia kakoj celi eto delaetsia**

dlia bezopasnoj kommunikacii

**kakoj rezultat dolzen byt dostignut**

xoziajin dostup en dlia realno vaznogo kontakta, no ne publik uet lichnye rekvizity

---

## 75 — maskirovann yj telefon pri srocn oj situacii

**cto eto dolzno delat**

pri propazhe pitomca moz et byt dostupen vremennyj nomer ili zascischennaja pereadresacija

**pocemu eto nuzno**

xoziajinu nuzna bystraja sviaz, no publikacija lichnogo nomera na plakatax i v socialnyx setiax sozdaet dolgosrocn yj risk

**kak eto dolzno rabotat po logike**

vremennyj kanal

* rabotaet tolko vo vremia aktivnogo poisk a
* moz et blokirovat spam
* zapis yvaet zhaloby
* otkliucaetsia posle vozvrata pitomca

**dlia kakoj celi eto delaetsia**

dlia srocn oj sviazi bez bessrocn oj publicacii nomera

**kakoj rezultat dolzen byt dostignut**

svidetel moz et pozvonit, a posle zaversheniia poisk a kontakt perestaet rabotat

---

# privatnost profilia pitomca

## 76 — osnovnye urovni vidimosti

**cto eto dolzno delat**

profil moz et byt

* publicnym
* dostupnym zaregistrirovannym
* dostupnym podpiscikam
* dostupnym druzjam
* dostupnym semejnomu krugu
* dostupnym po ssylke
* zakrytym
* vrem enno skrytym

**pocemu eto nuzno**

ne kazdyj pitomec dolzen byt publicnym blogerom

**kak eto dolzno rabotat po logike**

bazovyj uroven zadajot maksimalnuju auditoriju, a otdelnye polia mogut byt esio bolee zakrytymi

**dlia kakoj celi eto delaetsia**

dlia prostogo kontrolia publicnosti

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et odnim dejstviem zakryt profil, ne redaktiruja kazdoe pole otdelno

---

## 77 — privatnost kazdogo razdela

**cto eto dolzno delat**

otdelno mozno nastroit

* osnovnye dannye
* fotografii
* albomy
* xoziaev
* druzej
* mesto
* sobytija
* dostizeniia
* dokumenty
* istoriju adopcii
* memorialnye materialy
* status mikrochipa
* osobye potrebnosti

**pocemu eto nuzno**

xoziajin moz et xotet publicnye fotografii, no skryt mesto, xoziaev i dokumenty

**kak eto dolzno rabotat po logike**

zakrytyj razdel ne dolzen popadat v poisk, predprosmotr ssylok, rekomendacii i vneshnie indeksy

**dlia kakoj celi eto delaetsia**

dlia tocnogo kontrolia raznyx kategorij dannyx

**kakoj rezultat dolzen byt dostignut**

socialnaja aktivnost moz et byt publicnoj bez raskrytiia cuvstitelnoj istorii

---

## 78 — skrytie xoziajina

**cto eto dolzno delat**

publicnyj profil pitomca moz et ne pokaz yvat polnoe imia xoziajina

**pocemu eto nuzno**

populiarnyj profil pitomca moz et privlekat nezhelatelnoe vnimanie k realnomu celoveku

**kak eto dolzno rabotat po logike**

mozno pokazat

* upravliaetsia xoziajinom
* publicnoe imia
* tolko username
* organizaciju
* nichego, tolko knopku kontakta

platforma vse ravno znaet realnogo upravliajuscego

**dlia kakoj celi eto delaetsia**

dlia socialnogo profilia bez obyazatelnogo publicnogo raskrytiia lichnosti

**kakoj rezultat dolzen byt dostignut**

pitomec moz et imet otkrytyj profil, a xoziajin ostajotsia v ogranichennom rezhime vidimosti

---

## 79 — skrytie spiska sovladelcev

**cto eto dolzno delat**

xoziajin moz et pokaz yvat

* vsex upravliajuscix
* tolko osnovnogo xoziajina
* tolko organizaciju
* tolko kolichestvo
* nichego

**pocemu eto nuzno**

spisok sovladelcev moz et raskryvat semejnye i lichnye otnosheniia

**kak eto dolzno rabotat po logike**

skrytie publicnogo spiska ne meniaet realnye prava i audit vnutri sistemy

**dlia kakoj celi eto delaetsia**

dlia privatnosti semji

**kakoj rezultat dolzen byt dostignut**

u pitomca moz et byt neskolko upravliajuscix bez publicacii vsej semejnoj struktury

---

## 80 — publicnaja lokacija

**cto eto dolzno delat**

profil moz et pokaz yvat

* stranu
* gorod
* rajon
* obobschonn uju oblast
* nichego

**pocemu eto nuzno**

lokacija polezna dlia druzej, sobytij i mestnyx grupp, no opasna pri slishkom tocnom raskrytii

**kak eto dolzno rabotat po logike**

publicnaja lokacija vybiraetsia vrucnuju i ne obnovliaetsia avtomaticeski iz gps

**dlia kakoj celi eto delaetsia**

dlia lokalnoj socialnoj polzy bez postojannoj slezhki

**kakoj rezultat dolzen byt dostignut**

pitomca mozno najti v kataloge vilniusa, no nelzia uznat, v kakom dome on zhivet

---

## 81 — zascita domashnego adresa

**cto eto dolzno delat**

domashnij adres ne dolzen pokazyvatsia

* v profile
* v media metadannyx
* v marshrutax
* v predprosmotre ssylki
* v api
* v poisk e
* v rekomendacijax
* v publicnom qr

**pocemu eto nuzno**

profil pitomca moz et ispolzovatsia dlia krazhi zivotnogo ili opredeleniia raspisaniia semji

**kak eto dolzno rabotat po logike**

dlia prakticeskix funkcij ispolzuetsia obobschonnaja zona ili vremennyj dostup konkretnomu ispolniteliu

**dlia kakoj celi eto delaetsia**

dlia fiziceskoj bezopasnosti pitomca i xoziaev

**kakoj rezultat dolzen byt dostignut**

socialnaja set ne sozdaet publicnyj katalog domashnix adresov zivotnyx

---

## 82 — skrytie reguliarno go raspisaniia

**cto eto dolzno delat**

publicnyj profil ne dolzen pokaz yvat tocn oe reguliarnoe vremia

* progulok
* otsutstviia xoziajina
* vizitov sittera
* raboty kamery
* poezdok
* kormlenija
* poseceniia odnih i teh ze mest

**pocemu eto nuzno**

predskazuemoe raspisanie moz et byt ispolzovano dlia presledovaniia ili krazhi

**kak eto dolzno rabotat po logike**

publicno mozno pokaz yvat prosh edshee sobytie ili obschuju aktivnost bez tocn oj povtoriajusc ejsia shemy

**dlia kakoj celi eto delaetsia**

dlia zascity realnogo obraza zizni semji

**kakoj rezultat dolzen byt dostignut**

profil moz et byt aktivnym, no ne raskryvaet, kogda dom oby cno pust

---

## 83 — medicinskie dannye ne vxodiat v publicnyj profil avtomaticeski

**cto eto dolzno delat**

diagnozy, lekarstva, analiz y i operacii dolzny xranitsia v otdelnom zascischennom module

**pocemu eto nuzno**

medicinskaja informacija cuvstitelna i moz et ispolzovatsia dlia moshennicestva, diskriminacii ili nezhelatelnyx sovetov

**kak eto dolzno rabotat po logike**

xoziajin moz et dobrovolno pokazat prostuju publicnuju metku

* est osobye potrebnosti
* nuzhen spokojnyj kontakt
* est ogranichenie aktivnosti
* nuzhno reguliarnoe lekarstvo pri poisk e

polnye podrobnosti ostajutsia zakrytymi

**dlia kakoj celi eto delaetsia**

dlia bezopasnoj socialnoj publicnosti bez raskrytiia medkartocki

**kakoj rezultat dolzen byt dostignut**

drugie polucajut neobxodimuju instrukciju, no ne polucajut polnuju istoriju zdorovja

---

## 84 — publicnye znachki zdorovja

**cto eto dolzno delat**

po zhelaniju mozno pokazat obobschonnye statusy

* mikrochip est
* vakcinacija aktualna
* sterilizovan
* est osobye potrebnosti
* nuzhen dostupnyj marshrut
* neobxodim spokojnyj kontakt

**pocemu eto nuzno**

nekotorye bazovye statusy polezny dlia sobytij, adopcii i perederzki

**kak eto dolzno rabotat po logike**

znachok dolzen pokaz yvat istochnik i ne raskryvat dokument avtomaticeski

naprimer podtverzhdeno klinikoj ili ukazano xoziajinom

**dlia kakoj celi eto delaetsia**

dlia bystroj proverki obsch ix trebovanij

**kakoj rezultat dolzen byt dostignut**

organizator vidit relevantnyj status, no ne polucaet polnyj pasport ili medkartocku

---

## 85 — poiskovaia vidimost

**cto eto dolzno delat**

xoziajin moz et razresit ili zapretit poisk profilia po

* imeni
* username
* vidu
* porode
* gorodu
* interesam
* sobytijam
* gruppam
* qr ssylke

**pocemu eto nuzno**

publicnyj profil ne obyazatelno dolzen byt legko naxodimym po vsem priznakam

**kak eto dolzno rabotat po logike**

poiskovaia vidimost i dostup po priamoj ssylke nast r aivajutsia otdelno

**dlia kakoj celi eto delaetsia**

dlia kontrolia obnaruzheniia profilia

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et pokazat profil druzjam po ssylke, no ubrat ego iz globalnogo kataloga

---

## 86 — indeksacija vo vneshnem poiske

**cto eto dolzno delat**

xoziajin moz et razresit ili zapretit indeksaciju publicnoj stranicy vneshnimi poiskovymi sistemami

**pocemu eto nuzno**

publicnaja stranica vnutri socialnoj seti i globalnaia dostupnost vo vsem internete imejut raznye posledstviia

**kak eto dolzno rabotat po logike**

pri otkliuchenii nuzno ubrat profil iz

* publicnyx kart sajta
* strukturirovannyx publicnyx dannyx
* avtomaticeskix predprosmotrov
* vneshnej indeksacii po mere obnovleniia

**dlia kakoj celi eto delaetsia**

dlia kontrolia dolgosrocn oj publicnosti

**kakoj rezultat dolzen byt dostignut**

pitomec moz et imet profil dlia vnutrennego soobscestva bez obyazatelnogo prisutstviia vo vsem internete

---

## 87 — blokirovka polzovatelia na urovne profilia pitomca

**cto eto dolzno delat**

xoziajin moz et zablokirovat celoveka ot vzaimodejstviia s konkretnym pitomcem ili so vsemi svoimi profiljami

**pocemu eto nuzno**

inogda konflikt sviazan tolko s odnim profil em, a inogda trebuetsia polnaia blokirovka xoziajina i vseh ego upravliaemyx profilej

**kak eto dolzno rabotat po logike**

pri blokirovke nuzno obnovit

* prosmotr profilia
* soobsenija
* upominanija
* druzhbu
* gruppy
* sobytija
* rekomendacii
* dostup po star ym ssylkam
* komentarii

**dlia kakoj celi eto delaetsia**

dlia realnogo prekrashcheniia nezhelatelnogo kontakta

**kakoj rezultat dolzen byt dostignut**

zablokirovannyj celovek ne moz et obojti zascitu cerez profil drugogo pitomca ili star y j url

---

# vladenie i upravlenie

## 88 — osnovnoj xoziajin

**cto eto dolzno delat**

odin ili neskolko polzovatelej mogut imet status osnovnogo xoziajina v zavisimosti ot modeli vladen iia

**pocemu eto nuzno**

pitomec moz et prinadlezhat odnomu celoveku, pare, semje ili organizacii

**kak eto dolzno rabotat po logike**

platforma dolzna razdeliat

* publicnoe predstavlenie
* administrativnoe vladenie profilem
* dokumentalnoe vladenie
* pravo prinimat kriticeskie resheniia

**dlia kakoj celi eto delaetsia**

dlia podderzki realnyx semejnyx i organizacionnyx scenariev

**kakoj rezultat dolzen byt dostignut**

profil ne zavisit ot odnogo telefona ili akkaunta, no kriticeskie prava ostajutsia kontroliruemymi

---

## 89 — sovladelec

**cto eto dolzno delat**

sovladelec moz et poluchit prava

* redaktirovat profil
* publikovat
* upravliat privatnostiu
* dobavliat dokumenty
* upravliat dnevnikom
* videt medkartocku
* vkliucat poisk pri propazhe
* upravliat ustrojstvami
* priglashat sittera

**pocemu eto nuzno**

v semje ob pitomce mogut ravno otvetstvenno zabotitsia neskolko liudej

**kak eto dolzno rabotat po logike**

prava sovladelca nast r aivajutsia individualno

ne kazdyj sovladelec obyazatelno moz et peredavat vladenie ili udal iat profil

**dlia kakoj celi eto delaetsia**

dlia sovmestnogo upravleniia bez obschego parolia

**kakoj rezultat dolzen byt dostignut**

kazdyj dejstvuet pod svoim akkauntom, a istoria pokaz yvaet realnogo avtora

---

## 90 — clen semji

**cto eto dolzno delat**

clen semji moz et poluchit dostup k socialnym i bytovym funkcii iam bez polnogo administrativnogo vladen iia

**pocemu eto nuzno**

rebenku, rodstvenniku ili partneru ne vsegda nuzny prava na peredacu profilia, eksport medkartocki ili izmenenie xoziaev

**kak eto dolzno rabotat po logike**

rol daet bazovye prava

* publikovat po razreshenii
* vypolniat zadaci
* dobavliat fotografii
* videt raspisanie
* otmecat kormlenie
* ucavstvovat v semejnom chat e

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo ucastiia v uxode

**kakoj rezultat dolzen byt dostignut**

semja moz et polnocenno pomogat, ne polucaja lishnix kriticeskix prav

---

## 91 — sitter ili vremennyj smotritel

**cto eto dolzno delat**

sitter moz et poluchit vremennyj dostup k konkretnym funkcii iam pitomca

**pocemu eto nuzno**

emu nuzny instrukcii, zadaci, ekstrennye kontakty i, vozmozhno, gps na vremia progulki, no ne vsia istoria profilia

**kak eto dolzno rabotat po logike**

dostup ogranichen

* srokom
* razdelami
* mestom
* vremenem smeny
* konkretnymi ustrojstvami
* zapretom eksporta
* auditom

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo vremennogo uxoda

**kakoj rezultat dolzen byt dostignut**

sitter polucaet vse neobxodimoe dlia raboty i avtomaticeski ter iaet dostup posle zaversheniia uslugi

---

## 92 — priut kak upravliajuscij

**cto eto dolzno delat**

proverennyj priut moz et upravliat profil em zivotnogo do adopcii

**pocemu eto nuzno**

priutskij pitomec moz et ne imet individualnogo postojannogo xoziajina

**kak eto dolzno rabotat po logike**

profil prinadlezit organizacii, a sotrudniki polucajut rolevye prava

* redaktor
* veterinar
* koordinator adopcii
* fotograf
* volonter
* administrator

**dlia kakoj celi eto delaetsia**

dlia otsutstviia zavisimosti ot lichnogo akkaunta odnogo sotrudnika

**kakoj rezultat dolzen byt dostignut**

posle uvolneniia volontera profil ne ter iaetsia i ostajotsia pod kontrolem organizacii

---

## 93 — vremennaja perederzka

**cto eto dolzno delat**

perederzka moz et upravliat povsednevnym uxodom i publikacijami, no ne obyazatelno imet pravo na adopcionnoe reshenie

**pocemu eto nuzno**

zivotnoe fakticeski zhivet u volontera, no otvetstvennaja organizacija ostajotsia priutom

**kak eto dolzno rabotat po logike**

perederzka polucaet prava

* dnevnik
* fotografii
* nabludenija
* med icinskie zadaci
* socialnye posty po pravilam
* ekstrenn yj kontakt

adopcionnye dokumenty i peredaca vladen iia ostajutsia u organizacii

**dlia kakoj celi eto delaetsia**

dlia razdelenija ezhednevnogo uxoda i administrativnoj otvetstvennosti

**kakoj rezultat dolzen byt dostignut**

perederzka moz et polnocenno rasskazyvat o pitomce i zabotitsia o nem, ne prisvaivaja ego profil

---

## 94 — administrator bez prava vladen iia

**cto eto dolzno delat**

xoziajin moz et naznacit celoveka dlia texniceskogo upravleniia profilem

**pocemu eto nuzno**

populiarnym profilem moz et pomogat upravliat redaktor, fotograf ili socialnyj menedzer

**kak eto dolzno rabotat po logike**

administrator moz et

* publikovat
* moderirovat kommentarii
* upravliat albomami
* plan iro vat posty

no ne moz et

* peredavat pitomca
* videt medkartocku
* menjat mikrochip
* udal iat osnovnogo xoziajina
* otkryvat gps
* upravliat platezami bez otdelnogo prava

**dlia kakoj celi eto delaetsia**

dlia delegirovaniia socialnoj raboty bez peredaci polnogo kontrolia

**kakoj rezultat dolzen byt dostignut**

profilem mozno upravliat komandoj, no fiziceskaia i medicinskaja bezopasnost ne narushaetsia

---

## 95 — prava na publikaciju ot imeni pitomca

**cto eto dolzno delat**

xoziajin dolzen razresit, kto moz et

* sozdaivat posty
* publikovat istorii
* dobavliat media
* kommentirovat
* otvecat v chat e
* sozdaivat sobytija
* ucavstvovat v gruppax
* publikovat obnavleniia adopcii

**pocemu eto nuzno**

ne kazdyj smotritel ili clen semji dolzen avtomaticeski govorit ot imeni pitomca publicno

**kak eto dolzno rabotat po logike**

pravo na publikaciju i pravo na uxod nast r aivajutsia otdelno

**dlia kakoj celi eto delaetsia**

dlia kontrolia publicnogo obraza profilia

**kakoj rezultat dolzen byt dostignut**

sitter moz et otmetit kormlenie, no ne moz et bez razresheniia opublikovat reklamn yj post ot imeni pitomca

---

## 96 — ukazanie realnogo avtora

**cto eto dolzno delat**

riadom s dejstviem moz et byt dostupna informacija

`opublikovano andreem ot imeni baksa`

**pocemu eto nuzno**

u pitomca moz et byt neskolko upravliajuscix, i vazno ponimat realnogo avtora pri spore ili narushenii

**kak eto dolzno rabotat po logike**

publicnoe otobrazhenie moz et byt kompaktnym, no vnutrennij audit vsegda xranit realnogo avtora

**dlia kakoj celi eto delaetsia**

dlia otvetstvennosti i prozracnosti

**kakoj rezultat dolzen byt dostignut**

moderacija moz et obr atitsia k konkretnomu celoveku, a ne nakazyvat abstraktnogo pitomca

---

## 97 — kriticeskie prava

**cto eto dolzno delat**

otdelno nuzno upravliat pravami

* peredat vladenie
* udal it profil
* otkryt medkartocku
* otkryt gps
* upravliat kamerami
* izmenit mikrochip
* izmenit osnovnogo xoziajina
* publikovat adopciju
* zapustit marketplace sdelku
* vkliucit memorialnyj rezhim

**pocemu eto nuzno**

eti dejstviia imejut znacitelnye posledstviia i ne dolzny byt dostupny kazdomu redaktoru

**kak eto dolzno rabotat po logike**

moz et potrebovatsia

* mfa
* povtornyj parol
* soglasie vtorogo xoziajina
* period otmeny
* audit
* proverka dokumentov

**dlia kakoj celi eto delaetsia**

dlia zascity ot slucajnoj ili moshenniceskoj poteri kontrolia

**kakoj rezultat dolzen byt dostignut**

odin vzlomannyj akkaunt sittera ne moz et peredat ili udal it profil pitomca

---

## 98 — dva osnovnyx xoziajina

**cto eto dolzno delat**

profil moz et imet neskolko ravnopravnyx osnovnyx xoziaev

**pocemu eto nuzno**

pitomec moz et prinadlezhat pare ili semje bez odnogo dominantnogo akkaunta

**kak eto dolzno rabotat po logike**

kriticeskie dejstviia mozno nastroit

* dostatocno odnogo podtverzhdeniia
* nuzno podtverzhdenie dvux
* nuzno podtverzhdenie vseh
* nuzhen administrator organizacii

**dlia kakoj celi eto delaetsia**

dlia podderzki realnogo sovmestnogo vladen iia

**kakoj rezultat dolzen byt dostignut**

odin sovladelec ne moz et nezametno peredat pitomca ili udal it profil protiv soglasiia drugogo

---

## 99 — raznoglasie sovladelcev

**cto eto dolzno delat**

pri konflikte sistema dolzna vrem enno zascitit profil ot neobratimyx izmenenij

**pocemu eto nuzno**

sovladelcy mogut posporit o peredace, adopcii, medkartocke ili udaleni i

**kak eto dolzno rabotat po logike**

pri spore mozno

* zamorozit peredacu
* soxranit dostup k bazovomu uxodu
* zapretit udaleniie
* zaprosit dokumenty
* peredat slucaj specialnoj proverke
* soxranit audit
* ne publikovat lichnye obvineniia

**dlia kakoj celi eto delaetsia**

dlia zascity pitomca i dannyx do razresheniia spora

**kakoj rezultat dolzen byt dostignut**

profil ne stanovitsia instrumentom davleniia odnogo sovladelca na drugogo

---

## 100 — dokazatelstvo sviazi s pitomcem

**cto eto dolzno delat**

pri zaprose vladen iia mozno predostavit

* dokument
* registraciju mikrochipa
* starye fotografii
* veterinar nuju kartu
* adopcionnyj dogovor
* podtverzhdenie priuta
* svidetelstvo organizacii
* istoriju profilia
* skrytye primety
* podtverzhdenie tekuscego xoziajina

**pocemu eto nuzno**

publicnye fotografii mozno skopirovat, poetomu odn oj fotografii nedostatochno

**kak eto dolzno rabotat po logike**

dokazatelstva xran iatsia zakryto i dostupny tolko proveriajuscej storone

**dlia kakoj celi eto delaetsia**

dlia zascity ot prisvoeniia profilia i samogo zivotnogo

**kakoj rezultat dolzen byt dostignut**

realnyj xoziajin moz et podtverdit sviaz, a moshennik ne moz et poluchit profil tolko po fotografii iz interneta

---

# peredaca vladen iia

## 101 — oby cnaia peredaca profilia

**cto eto dolzno delat**

tekuscij osnovnoj xoziajin moz et bezopasno peredat profil novomu

**pocemu eto nuzno**

pitomec moz et byt podaren, adoptirovan, peredan rodstvenniku ili smenit postojannyj dom

**kak eto dolzno rabotat po logike**

process dolzen soderzhat

1. vybor novogo xoziajina
2. proverku ego akkaunta
3. vybor daty peredaci
4. vybor peredavaemyx dannyx
5. obrabotku mikrochipa
6. obrabotku medkartocki
7. obrabotku socialnogo kontenta
8. podtverzhdenie novogo xoziajina
9. period otmeny pri neobxodimosti
10. audit

**dlia kakoj celi eto delaetsia**

dlia nepreryvnoj i prozracnoj smeny otvetstvennosti

**kakoj rezultat dolzen byt dostignut**

novyj xoziajin polucaet upravlenie i vaznuju istoriju, a staryj ter iaet lishnie prava

---

## 102 — cto peredaetsia novomu xoziajinu

**cto eto dolzno delat**

mozno otdelno obrabotat

* osnovnoj profil
* publicnye posty
* fotografii
* medkartocku
* vakcinacii
* mikrochip
* dnevnik uxoda
* ustrojstva
* dokumenty
* straxovku
* marketplace zakazy
* socialnye sviazi
* gruppy
* sobytija
* adopcionnye materialy

**pocemu eto nuzno**

ne vse dannye prinadlezhat tolko pitomcu

nekotorye soderzhat lichnuju informaciju starogo xoziajina

**kak eto dolzno rabotat po logike**

sistema dolzna razdeliat dannye pitomca i lichnye dannye predyduscego xoziajina

**dlia kakoj celi eto delaetsia**

dlia peredaci poleznoj istorii bez utecki cuzoj privatnosti

**kakoj rezultat dolzen byt dostignut**

novyj xoziajin polucaet vakcinacii i lekarstva, no ne polucaet lichnye chat y i plateznye rekvizity starogo xoziajina

---

## 103 — prava predyduscego xoziajina posle peredaci

**cto eto dolzno delat**

staryj xoziajin moz et poluchit odin iz statusov

* polnostiu udal en iz upravleniia
* ostalsia podpis c ikom
* ostalsia sovladelcem po soglasiju
* vidit tolko publicnyj profil
* polucaet ogranichennye obnovleniia po soglasiju
* ne imeet nikakogo dostup a

**pocemu eto nuzno**

avtomaticeskoe soxranenie polnogo dostup a posle peredaci narushaet privatnost novogo doma

**kak eto dolzno rabotat po logike**

novyj xoziajin dolzen podtverdit liuboj prodolzajusc ijsia dostup

**dlia kakoj celi eto delaetsia**

dlia jasnogo zaversheniia predyduscego vladen iia

**kakoj rezultat dolzen byt dostignut**

staryj xoziajin ne moz et prodolzat smotret gps, kamery i medkartocku bez soglasiia novogo

---

## 104 — peredaca iz priuta

**cto eto dolzno delat**

priut moz et zaversit adopciju i peredat profil

**pocemu eto nuzno**

adopcija dolzna byt ne tolko izmeneniem statusa objavlenija, no i polnym izmeneniem otvetstvennosti

**kak eto dolzno rabotat po logike**

priut

* proveriaet kandidata
* podtverzhdaet dokumenty
* vybiraet peredavaemye dannye
* obnovliaet mikrochip
* sozdaet posle adopcionnyj chat
* ustanavlivaet kontrolnye daty
* zakryvaet publicn uju adopcionnuju formu

**dlia kakoj celi eto delaetsia**

dlia celostnogo zaversheniia adopcii

**kakoj rezultat dolzen byt dostignut**

pitomec ne ostajotsia odnovremenno v statuse ishchet dom i uze zhivet u novogo xoziajina

---

## 105 — vremennaja peredaca na perederzku

**cto eto dolzno delat**

profil moz et byt vrem enno peredan dlia uxoda bez smeny osnovnogo vladen iia

**pocemu eto nuzno**

otpusk, lechenie xoziajina ili perederzka ne dolzny meniat postojannogo xoziajina

**kak eto dolzno rabotat po logike**

nuzno ukazat

* period
* mesto
* otvetstvennogo
* prava
* instrukcii
* ekstrennye kontakty
* med icinskie ogranicheniia
* avtomaticeskoe zavershenie

**dlia kakoj celi eto delaetsia**

dlia vremennogo uxoda bez administrativnoj putanicy

**kakoj rezultat dolzen byt dostignut**

perederzka polucaet nuzn yj dostup, no osnovnoj xoziajin ne ter iaet profil

---

## 106 — sporn oe vladenie

**cto eto dolzno delat**

pri protivorechivyx zayavlenijax profil moz et poluchit status spornogo vladen iia

**pocemu eto nuzno**

dva celoveka mogut predostavit raznye dokumenty ili utverzhdat, cto pitomec byl ukraden

**kak eto dolzno rabotat po logike**

na vremia proverki mozno

* zablokirovat peredacu
* zablokirovat udaleniie
* skryt tocn uju lokaciju
* soxranit bazovyj uxod
* proverit chip
* proverit dokumenty
* privlec organizaciju
* soxranit dokazatelstva
* ogranichit publicnye obvineniia

**dlia kakoj celi eto delaetsia**

dlia zascity pitomca i profilia do ustanovleniia prav

**kakoj rezultat dolzen byt dostignut**

ni odna storona ne moz et unictozit ili ukrast cifrovuju istoriju vo vremia spora

---

# socialnye sviazi pitomca

## 107 — podpiska na profil pitomca

**cto eto dolzno delat**

drugie polzovateli mogut podpisatsia na publicnye obnovleniia pitomca

**pocemu eto nuzno**

podpiska ne obyazatelno oznachaet lichnuju druzhbu ili dostup k zakrytym dannym

**kak eto dolzno rabotat po logike**

xoziajin nast r aivaet

* kto moz et podpisatsia
* nuzno li odobrenie
* cto vidat podpisciki
* mogut li pisat
* pokazyvaetsia li spisok podpiscikov

**dlia kakoj celi eto delaetsia**

dlia publicnogo interesa bez obyazatelnogo dvustoronnego kontakta

**kakoj rezultat dolzen byt dostignut**

populiarnyj profil moz et imet auditoriju, ne prevrashchaja vseh podpiscikov v druzej xoziajina

---

## 108 — druzhba mezhdu pitomcami

**cto eto dolzno delat**

dva profilia pitomcev mogut imet podtverzhdennuju socialnuju sviaz

**pocemu eto nuzno**

druz ia pitomca vazny dlia progulok, sobytij, albomov i rekomendacij

**kak eto dolzno rabotat po logike**

zapros prinimajut upravliajusc ie obeix profilej

druzhba ne dolzna avtomaticeski davat dostup k telefonam, adresam, gps ili medkartockam

**dlia kakoj celi eto delaetsia**

dlia socialnoj seti, orientirovannoj na realnye sviazi pitomcev

**kakoj rezultat dolzen byt dostignut**

profilej mogut byt sviazany kak druzia, a privatnost xoziaev ostajotsia otdelnoj

---

## 109 — tip druzhby

**cto eto dolzno delat**

mozno ukazat

* znakom y po parku
* blizkie druzia
* zhivut vmeste
* rodstvenniki
* uchastniki treninga
* parallel n y e progulki
* druzia po sobytiju
* znakomstvo bez blizkogo kontakta

**pocemu eto nuzno**

ne vse socialnye sviazi odinakovy

**kak eto dolzno rabotat po logike**

tip moz et byt publicnym ili zakrytym i ne dolzen sozdaivat garantiju bezopasnoj sovmestimosti

**dlia kakoj celi eto delaetsia**

dlia tocnogo socialnogo konteksta

**kakoj rezultat dolzen byt dostignut**

drugie ponimajut, kak pitomcy znakom y, no ne schitajut odnu metku professionalnoj ocenk oj povedeniia

---

## 110 — blizkij krug pitomca

**cto eto dolzno delat**

xoziajin moz et sozdat ogranichenn yj krug profilej i liudej

**pocemu eto nuzno**

blizkim druzjam mozno pokaz yvat bolshe, chem oby cnym podpiscikam

**kak eto dolzno rabotat po logike**

blizkij krug moz et videt

* zakrytye istorii
* priglasheniia na progulki
* obschie albomy
* vrem ennyj status
* bazovye instrukcii
* srocn oe uvedomlenie o propazhe

**dlia kakoj celi eto delaetsia**

dlia ogranichennogo doveritelnogo socialnogo prostranstva

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et delitsia lichnymi momentami bez publicacii vsem podpiscikam

---

## 111 — rodstvennye sviazi

**cto eto dolzno delat**

mozno sviazat

* mat
* otca
* potomka
* brata
* sestru
* odin pomet
* predpolagaemoe rodstvo
* dokumentalno podtverzhdennoe rodstvo

**pocemu eto nuzno**

rodstvennye sviazi polezny dlia istorii, zavodcikov, adopcii i sem ejnyx albomov

**kak eto dolzno rabotat po logike**

sviaz dolzna byt podtverzhdena upravliajusc imi profiljami ili dokumentom

ona ne dolzna avtomaticeski dokaz yvat geneticeskoe rodstvo bez osnovaniia

**dlia kakoj celi eto delaetsia**

dlia sozdaniia proveriaemoj sem ejnoj istorii zivotnogo

**kakoj rezultat dolzen byt dostignut**

profilej mozno sviazat, no urov en podtverzhdeniia vid en polzovateliam

---

## 112 — rodoslovnaja

**cto eto dolzno delat**

dlia podxodiascix zivotnyx profil moz et soderzhat strukturirovannuju rodoslovnuju

**pocemu eto nuzno**

prostaja fotografija dokumenta ne daet udobnogo poiska i sviazi mezhdu profiljami

**kak eto dolzno rabotat po logike**

mozno ukazat

* organizaciju
* nomer
* roditelej
* pokoleniia
* status proverki
* dokument
* ograniceniia publicnosti

**dlia kakoj celi eto delaetsia**

dlia istorii i professionalnogo konteksta

**kakoj rezultat dolzen byt dostignut**

rodoslovnaja dostupna kak strukturirovannaja informacija, no ne ispolzuetsia dlia diskriminacii oby cnyx pitomcev

---

## 113 — sovmestnye albomy druzej

**cto eto dolzno delat**

dva ili neskolko profilej mogut imet obsch ij album

**pocemu eto nuzno**

fotografii gruppovoj progulki ili sovmestnoj zizni ne dolzny dublir ovatsia v kazdom profile

**kak eto dolzno rabotat po logike**

dlia dobavleniia fotografii nuzno ucit yvat prava vseh profilej i soglasie na tag

**dlia kakoj celi eto delaetsia**

dlia udobnoj obschej istorii

**kakoj rezultat dolzen byt dostignut**

odno media moz et byt sviazano s neskolkimi pitomcami bez sozdaniia neskolkix fiziceskix kopij

---

## 114 — tag pitomca na fotografii

**cto eto dolzno delat**

polzovatel moz et otmetit profil pitomca na media

**pocemu eto nuzno**

eto pomogaet sozdavat sviazi mezhdu kontentom i profiljami

**kak eto dolzno rabotat po logike**

xoziajin profil ia moz et nastroit

* razresat avtomaticeski druzjam
* trebovat odobrenie
* zapretit
* razresat tolko v zakrytyx albomax
* udal iat metku

**dlia kakoj celi eto delaetsia**

dlia kontrolia prisutstviia pitomca v cuzo m kontente

**kakoj rezultat dolzen byt dostignut**

pitomca nelzia massovo otmecat v reklame, moshenniceskix postax ili cuzix objavlenijax bez razresheniia

---

## 115 — upominanie profilia

**cto eto dolzno delat**

username pitomca moz et ispolzovatsia v postax, kommentariiax, gruppax i sobytijax

**pocemu eto nuzno**

upominanie pomogaet sviazyvat diskussii i priglasheniia

**kak eto dolzno rabotat po logike**

xoziajin moz et nastroit

* upominaniia vsem
* tolko druzjam
* tolko v gruppax
* s predvaritelnym odobreniem
* polnostiu zapretit

**dlia kakoj celi eto delaetsia**

dlia socialnogo vzaimodejstviia bez massovogo spama

**kakoj rezultat dolzen byt dostignut**

profil moz et byt upomian ut v relevantnom kontekste, no ne ispolzuetsia dlia nakrutki i reklamy

---

# publikacii ot imeni pitomca

## 116 — posty profilia pitomca

**cto eto dolzno delat**

upravliajusc ie mogut publikovat

* fotografii
* video
* istorii
* tekst
* voprosy
* dostizeniia
* progulki
* sobytija
* obnavleniia
* adopcionnye istorii
* memorialnye materialy

**pocemu eto nuzno**

profil pitomca dolzen byt socialno aktivnym centrom, a ne staticeskoj anketoj

**kak eto dolzno rabotat po logike**

kazdyj post imeet realnogo avtora, auditoriju, status moderacii i sviazann yj profil

**dlia kakoj celi eto delaetsia**

dlia socialnoj istorii pitomca

**kakoj rezultat dolzen byt dostignut**

vse publikacii sobirajutsia v odnom profile i ne teriajutsia pri smene xoziajina bez produmannogo processa

---

## 117 — auditorija posta

**cto eto dolzno delat**

dlia kazdogo posta mozno vybrat

* vse
* zaregistrirovannye
* podpisciki
* druzia
* blizkij krug
* semejnoe prostranstvo
* konkretnaja gruppa
* tolko ja
* po ssylke

**pocemu eto nuzno**

raznye momenty imejut raznyj urov en privatnosti

**kak eto dolzno rabotat po logike**

auditorija posta ne dolzna byt sire bazovoj privatnosti profilia bez jasnogo podtverzhdeniia

**dlia kakoj celi eto delaetsia**

dlia kontrolliruemogo rasprostraneniia kontenta

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et delitsia semejnoj fotografiej tolko s blizkimi, a publicnym dostizeniem so vsemi

---

## 118 — cernoviki publikacij

**cto eto dolzno delat**

post moz et byt soxranen kak cernovik

**pocemu eto nuzno**

xoziajin moz et xotet podgotovit tekst, obrabotat fotografii, perevesti ili poluchit soglasie drugih liudej

**kak eto dolzno rabotat po logike**

cernovik dostup en tolko upolnomochennym redaktoram i ne pojavliaetsia v l ent e, poisk e ili uvedomlenijax

**dlia kakoj celi eto delaetsia**

dlia kachestvennoj i bezopasnoj podgotovki kontenta

**kakoj rezultat dolzen byt dostignut**

nezavershenn yj material ne publik uetsia slucajno

---

## 119 — zaplanirovannye publikacii

**cto eto dolzno delat**

post mozno opublikovat v ukazannoe vremia

**pocemu eto nuzno**

eto polezno dlia professionalnyx profilej, priutov, sobytij i mnogoiazy cnyx publikacij

**kak eto dolzno rabotat po logike**

pered publikaciej sistema povtorno proveriaet

* prava avtora
* status profilia
* privatnost
* dostupnost media
* blokirovku
* aktualnost adopcionnogo ili poiskovogo statusa

**dlia kakoj celi eto delaetsia**

dlia upravljaemogo kontent plana

**kakoj rezultat dolzen byt dostignut**

post ne publik uetsia ot imeni profilia, kotoryj byl peredan, zablokirovan ili pereveden v memorialnyj rezhim

---

## 120 — redaktirovanie i istoria versij

**cto eto dolzno delat**

upravliajusc ij moz et ispravit tekst, media, auditoriju ili opisanie

**pocemu eto nuzno**

v publikacii mogut byt oshibki ili slucajno raskrytye dannye

**kak eto dolzno rabotat po logike**

sushestvennye izmeneniia imejut metku izmeneno i istoriju dlia moderacii

**dlia kakoj celi eto delaetsia**

dlia ispravleniia bez skrytogo perepisyvaniia konfliktov ili dokazatelstv

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et ubrat adres ili opечатku, a moderator moz et vosstanovit kontekst zhaloby

---

## 121 — udaleniie posta

**cto eto dolzno delat**

avtor ili upolnomochennyj upravliajusc ij moz et udal it publikaciju

**pocemu eto nuzno**

kontent moz et stat neaktualnym, slucajno raskryvat dannye ili byt opublikovan bez soglasiia

**kak eto dolzno rabotat po logike**

nuzno razdeliat

* skryt iz profilia
* udal it dlia vseh
* arhivirovat
* soxranit kak lichnyj material
* vrem enno skryt na proverku

**dlia kakoj celi eto delaetsia**

dlia kontrolia nad socialnoj istoriej

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et ubrat kontent, a dokazatelstva serioznoj zhaloby ne unictozhajutsia do zaversheniia proverki

---

## 122 — avtomaticeskie publikacii iz drugih modulej

**cto eto dolzno delat**

po soglasiju mozno sozdat post iz

* sobytija
* progulki
* dostizeniia
* konkursa
* adopcii
* poisk a poterjannogo pitomca
* vosstanovleniia posle poisk a
* professionalnoj fotosessii
* novogo znachka

**pocemu eto nuzno**

polzovatel ne dolzen povtorno zagruzhat odni i te ze dannye

**kak eto dolzno rabotat po logike**

nikakoe sobytie ne dolzno avtomaticeski stanovitsia publicnym bez nast roen n ogo soglasiia ili podtverzhdeniia

**dlia kakoj celi eto delaetsia**

dlia udobnogo sviazyvaniia funkcii bez utecki privatnosti

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et odnim dejstviem podelitsia dostizeniem, no medkartocka ili gps ne publikujutsia sami

---

# dostizeniia i statusy

## 123 — znachki pitomca

**cto eto dolzno delat**

profil moz et polucat znachki

* pervaja gruppovaja progulka
* uchastnik volonterskogo sobytija
* uchastnik kursa
* sportsmen
* vystavocnoe dostizhenie
* spas en iz priuta
* donor pri professionalnom podtverzhdenii
* umnyj adresnik podkliuchen
* profil mikrochipa podtverzhden

**pocemu eto nuzno**

znachki mogut pokaz yvat istoriju i dostizeniia, no ne dolzny sozdaivat loznoe medicinskoe ili professionalnoe doverie

**kak eto dolzno rabotat po logike**

nuzno razdeliat

* avtomaticeskie
* organizacionnye
* professionalno podtverzhdennye
* lichnye
* vremennye
* publicnye
* skrytye

**dlia kakoj celi eto delaetsia**

dlia vizualn oj istorii bez preuvelicheniia

**kakoj rezultat dolzen byt dostignut**

kazdyj znachok imeet pon iatnoe proisxozdenie i ne oznachaet bolshe, chem realno podtverzhdeno

---

## 124 — dostizeniia

**cto eto dolzno delat**

mozno dobavit

* konkurs
* vystavku
* sport
* treningovyj ekzamen
* blagotvoritelnoe uchastie
* spasateln uju dejatelnost
* kurs
* sertifikat
* lichn yj etap

**pocemu eto nuzno**

socialnaja istorija pitomca moz et byt bogace oby cn yx fotografij

**kak eto dolzno rabotat po logike**

dostizenie imeet

* nazvanie
* datu
* organizatora
* rezultat
* dokument
* fotografii
* status proverki
* auditoriju

**dlia kakoj celi eto delaetsia**

dlia strukturirovannogo soxraneniia vaznyx momentov

**kakoj rezultat dolzen byt dostignut**

dostizeniia ne teriajutsia v l ent e i mogut byt provereny pri neobxodimosti

---

## 125 — status sluzebnogo ili rabochego zivotnogo

**cto eto dolzno delat**

profil moz et soderzhat proveriaem yj status

* sluzebnoe
* poiskovoe
* sportivnoe
* terapevticeskoe
* zivotnoe podderzki
* rabochee
* pensionirovannoe

**pocemu eto nuzno**

raznye statusy imejut raznoe znachenie v raznyx stranax i ne dolzny prisvaivatsia prostym flazkom

**kak eto dolzno rabotat po logike**

nuzno ukazat

* tip
* organizaciju
* region
* dokument
* srok
* cto imenno provereno
* cto status ne oznachaet

**dlia kakoj celi eto delaetsia**

dlia chestnogo i regionalno korrektnogo predstavleniia

**kakoj rezultat dolzen byt dostignut**

polzovatel ne moz et prostym nazhatiem vydat oby cnogo pitomca za oficialno sluzebnoe zivotnoe

---

## 126 — treningovye navyki

**cto eto dolzno delat**

mozno ukazat

* bazovye komandy
* bytovye navyki
* sportsmennye navyki
* poiskovuju rabotu
* spokojnoe ozhidanie
* privyk anie k perenoske
* uxodovye procedury
* navyki dostupnosti

**pocemu eto nuzno**

eta informacija polezna sitteru, kinologu, priutu i organizatoru sobytija

**kak eto dolzno rabotat po logike**

nuzno razdeliat

* ukazano xoziajinom
* podtverzhdeno trenerom
* sertificirovano organizaciej
* v processe obucheniia

**dlia kakoj celi eto delaetsia**

dlia chestnogo opisaniia umenij

**kakoj rezultat dolzen byt dostignut**

drugie ponimajut, kakie navyki realno podtverzhdeny, a kakie esio trenirujutsia

---

# integracija s osnovnymi moduljami

## 127 — integracija s medkartockoj

**cto eto dolzno delat**

profil pitomca dolzen byt osnovnym vladelcem sviazi s ego medkartockoj

**pocemu eto nuzno**

bez odinakovogo identifikatora analiz y, vakciny i lekarstva mogut byt priviazany ne k tomu zivotnomu

**kak eto dolzno rabotat po logike**

publicnyj profil i medkartocka imejut raznye prava, no ssylajutsia na odnogo pitomca

**dlia kakoj celi eto delaetsia**

dlia celostnosti medicinskoj istorii

**kakoj rezultat dolzen byt dostignut**

smena avatara ili xoziajina ne sozdaet novuju pustuju medkartocku

---

## 128 — integracija s dnevnikom uxoda

**cto eto dolzno delat**

vse kormleniia, progulki, son, voda i zadaci dolzny byt priviazany k konkretnomu profilem

**pocemu eto nuzno**

v dome moz et byt neskolko pitomcev s raznymi rutin ami

**kak eto dolzno rabotat po logike**

pri bystrom dejstvii polzovatel vidit, dlia kakogo pitomca ono sozdajotsia

sistema predotvrashaet slucajnuju zapis ne tomu zivotnomu

**dlia kakoj celi eto delaetsia**

dlia tocnogo ezhednevnogo uxoda

**kakoj rezultat dolzen byt dostignut**

kormlenie luny ne pojavliaetsia v dnevnik e baksa

---

## 129 — integracija s gps i ustrojstvami

**cto eto dolzno delat**

ustrojstvo moz et byt priviazano k profilem pitomca

**pocemu eto nuzno**

odin xoziajin moz et imet neskolko gps, kormushek i datchikov

**kak eto dolzno rabotat po logike**

profil dolzen pokaz yvat

* aktivnye ustrojstva
* vladenie
* dostup
* poslednjuju sviaz
* zaryad
* status priviazki
* istoriju zam eny

**dlia kakoj celi eto delaetsia**

dlia edinogo centra texniceskogo uxoda

**kakoj rezultat dolzen byt dostignut**

pri smene gps istoria profilia soxraniaetsia, a st aro e ustrojstvo moz et byt bezopasno otviazano

---

## 130 — integracija s poterjannymi pitomcami

**cto eto dolzno delat**

iz profilia mozno odnim dejstviem zapustit poisk

**pocemu eto nuzno**

v srocn oj situacii nelzia z anovo vvodit vse identifikacionnye dannye

**kak eto dolzno rabotat po logike**

v cernovik poisk a podstavliajutsia

* identifikacionnaja fotografija
* imia
* vid
* okras
* razmer
* osobye primety
* mikrochip status
* ekipirovka
* bezopasnyj sposob podojti
* skrytye proverocnye primety

xoziajin proveriaet i publik uet

**dlia kakoj celi eto delaetsia**

dlia maksimalno bystrogo nacala poisk a

**kakoj rezultat dolzen byt dostignut**

kachestvennoe objavlenie moz et byt sozdano za minuty, a ne za cas

---

## 131 — integracija s najdennymi zivotnymi

**cto eto dolzno delat**

sistema moz et sopostavliat profil s kartockami najdennyx zivotnyx

**pocemu eto nuzno**

xoziajin moz et ne uvidet objavlenie, opublikovannoe v drugoj gruppe ili priute

**kak eto dolzno rabotat po logike**

sopostavlenie ucit yvaet

* fotografii
* vid
* okras
* pol
* razmer
* mesto
* vremia
* osobye primety
* mikrochip

okonchatelnoe podtverzhdenie ne delaetsia tolko algoritmom

**dlia kakoj celi eto delaetsia**

dlia uvelicheniia veroiatnosti vozvrata

**kakoj rezultat dolzen byt dostignut**

xoziajin polucaet relevantnye sovpadeniia, no zivotnoe ne peredajotsia po odn oj ai ocenke

---

## 132 — integracija s kartoj mest

**cto eto dolzno delat**

profil moz et xranit soxranennye i poseschennye mesta

* parki
* kliniki
* salony
* ploscadki
* kafe
* gostinicy
* marshruty
* mesta treninga

**pocemu eto nuzno**

raznym pitomcam podxodiat raznye mesta

**kak eto dolzno rabotat po logike**

xoziajin moz et otmetit

* liubimoe mesto
* poseschali
* xotim posetit
* ne podxodit
* est vaznaia zametka
* ne pokaz yvat publicno

**dlia kakoj celi eto delaetsia**

dlia personalnogo plan iro vaniia i rekomendacij

**kakoj rezultat dolzen byt dostignut**

sistema rekomenduet mesta s ucetom vida, razmera i predpoctenij, ne publik uja reguliarnoe raspisanie

---

## 133 — integracija s sobytijami

**cto eto dolzno delat**

pitomca mozno registrirovat na

* progulku
* trening
* vystavku
* vebinar
* fotosessiju
* adopcionnoe sobytie
* konkurs
* professionalnyj priem

**pocemu eto nuzno**

na odno sobytie xoziajin moz et privesti odnogo pitomca, a na drugoe drugogo

**kak eto dolzno rabotat po logike**

registracija sviazyvaet konkret n yj profil, no organizator polucaet tolko neobxodimye dannye

**dlia kakoj celi eto delaetsia**

dlia tocnogo spiska uchastnikov i bezopasnosti

**kakoj rezultat dolzen byt dostignut**

organizator znaet, kakoi pitomec pridot, no ne vidit vsiu ego medkartocku i domashnij adres

---

## 134 — integracija s gruppami

**cto eto dolzno delat**

profil pitomca moz et sosto iat v

* porodnyx gruppax
* mestnyx gruppax
* gruppax po interesam
* gruppax po zdorovju
* gruppax treninga
* gruppax priuta
* zakrytyx semejnyx gruppax

**pocemu eto nuzno**

xoziajin i pitomec mogut imet raznye socialnye konteksty

**kak eto dolzno rabotat po logike**

upravliajusc ij vybiraet, vstupaet li on lichnym profilem ili ot imeni pitomca

realnyj avtor vse ravno fiksiruetsia

**dlia kakoj celi eto delaetsia**

dlia tematiceskogo socialnogo ucastiia

**kakoj rezultat dolzen byt dostignut**

posty v gruppe sobak ne smeshivajutsia s lichnym professionalnym profilem xoziajina

---

## 135 — integracija s marketplace

**cto eto dolzno delat**

profil moz et ispolzovatsia dlia podbora

* odezhdy
* shleek
* perenosok
* kormushek
* lezh anok
* uslug
* arendy
* korma
* oborudovaniia

**pocemu eto nuzno**

razmery i vid pitomca mogut pomoch izb ezat nepodxodiasc ej pokupki

**kak eto dolzno rabotat po logike**

marketplace polucaet tolko razresennye prakticeskie parametry

med icinskie dannye ne dolzny ispolzovatsia dlia skrytoj reklamy ili povysheniia ceni

**dlia kakoj celi eto delaetsia**

dlia bolee relevantnogo podbora tovarov

**kakoj rezultat dolzen byt dostignut**

polzovatel vidit podxodiasc ie razmery, no magazin ne polucaet diagnozy i polnuju istoriju zdorovja

---

## 136 — integracija s ekspertami

**cto eto dolzno delat**

xoziajin moz et vybrat pitomca pri

* zapisi k veterinaru
* konsultacii kinologa
* gruminge
* reabilitacii
* sitter e
* transportirovke
* fotosessii

**pocemu eto nuzno**

specialistu nuzno ponimat, s kakim zivotnym sviazana usluga

**kak eto dolzno rabotat po logike**

posle vybora pitomca xoziajin otdelno reshaet, kakie dannye peredat

**dlia kakoj celi eto delaetsia**

dlia tocnogo konteksta pri minimalnom dostup e

**kakoj rezultat dolzen byt dostignut**

grumer vidit porodu, razmer i povedenceskie instrukcii, no ne polucaet analiz y krovi bez neobxodimosti

---

## 137 — integracija s adopciej

**cto eto dolzno delat**

profil moz et perejti v status ishchet dom i poluchit adopcionnyj razdel

**pocemu eto nuzno**

adopcionnoe objavlenie dolzno byt sviazano s realnym profilem, a ne sozdaivat otdelnuju kopiju

**kak eto dolzno rabotat po logike**

adopcionnyj modul dobavliaet

* uslovija
* anketu
* sovmestimost
* osobye potrebnosti
* istoriju zayavok
* vstrechi
* reshenie
* peredacu vladen iia

**dlia kakoj celi eto delaetsia**

dlia celostnogo processa ot publikacii do novogo doma

**kakoj rezultat dolzen byt dostignut**

posle adopcii tot ze profil prodolzaet zhit pod upravleniem novogo xoziajina

---

# dokumenty i identifikacija

## 138 — status mikrochipa v profile

**cto eto dolzno delat**

publicno mozno pokazat

* chip est
* chip ne ukazan
* chip neizvesten
* registracija proverena
* kontakty trebu jut obnovleniia

**pocemu eto nuzno**

nalichie chipa vazno dlia poisk a i identifikacii, no polnyj nomer cuvstitelen

**kak eto dolzno rabotat po logike**

polnyj nomer xranitsia v zakrytom module, a publicno pokazyvaetsia tolko status

**dlia kakoj celi eto delaetsia**

dlia poleznoj informacii bez raskrytiia identifikatora

**kakoj rezultat dolzen byt dostignut**

nashedshij znaet, cto nuzno proverit chip, no ne moz et skopirovat ego polnyj nomer iz profilia

---

## 139 — registracionnye nomera

**cto eto dolzno delat**

mozno xranit

* registraciju municipaliteta
* nomer kluba
* nomer priuta
* nomer rodoslovnoj
* nomer pasporta
* tatu i rovku
* drugoj identifikator

**pocemu eto nuzno**

u raznyx vidov i stran mogut byt raznye sistemy registracii

**kak eto dolzno rabotat po logike**

kazdyj nomer imeet tip, organizaciju, region, status proverki i privatnost

**dlia kakoj celi eto delaetsia**

dlia podderzki raznyx sistem bez odinogo neponiatnogo polia nomer

**kakoj rezultat dolzen byt dostignut**

identifikatory ne smeshivajutsia i mogut byt provereny po pravilnomu istochniku

---

## 140 — cifrovoj pasport

**cto eto dolzno delat**

profil moz et soderzhat ssylku na cifrovoj veterinarnyj ili identifikacionnyj pasport

**pocemu eto nuzno**

pasport vazen dlia poezdok, vakcinacij, chip a i klinik

**kak eto dolzno rabotat po logike**

nuzno razdeliat

* originalnyj cifrovoj dokument
* skan
* fotografiju
* neproverennuju kopiju
* vydann yj klinikoj dokument

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo xraneniia i peredaci dokumentov

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et bystro podgotovit dokumenty, no publicnyj profil ne otkryvaet pasport vsem

---

## 141 — proverka dokumentov

**cto eto dolzno delat**

dokument moz et imet status

* zagruzhen xoziajinom
* vydan proverenn oj organizaciej
* podtverzhden specialistom
* trebuet proverki
* iste k
* otmenen
* vozmozhno poddelan

**pocemu eto nuzno**

fotografija dokumenta sama po sebe ne dokazyvaet podlinnost

**kak eto dolzno rabotat po logike**

status dokumenta ne dolzen avtomaticeski rasprostraniatsia na vse ostalnye dannye profilia

**dlia kakoj celi eto delaetsia**

dlia chestnogo urovnia doverija

**kakoj rezultat dolzen byt dostignut**

organizator sobytija vidit, podtverzhden li konkretnyj sertifikat, a ne prosto nalichie fajla

---

## 142 — straxovka pitomca

**cto eto dolzno delat**

profil moz et imet zakrytuju sviaz so straxovym polisom

**pocemu eto nuzno**

straxovka otnositsia k pitomcu, no soderzit finansovye i lichnye dannye xoziajina

**kak eto dolzno rabotat po logike**

publicno mozno pokazat tolko po zhelaniju

`est straxovka`

polnye rekvizity dostupny tolko xoziajinu i upolnomochennym organizacijam

**dlia kakoj celi eto delaetsia**

dlia udobnogo upravleniia bez utecki finansov

**kakoj rezultat dolzen byt dostignut**

pri kliniceskom obrashenii polis legko najti, no nomer ne publik uetsia v socialnom profile

---

# istorija zizni

## 143 — vremennaja liniia pitomca

**cto eto dolzno delat**

profil moz et imet xronologiju

* rozdenie
* priut
* adopcija
* pervyj dom
* smena imeni
* pervaja progulka
* vakcinacija
* operacija
* dostizeniia
* puteshestvija
* druzia
* propazha i vozvrat
* smena xoziajina
* memorial

**pocemu eto nuzno**

profil pitomca moz et soderzhat mnogoletnjuju istoriju, kotoruju trudno ponimat po odn oj l ent e

**kak eto dolzno rabotat po logike**

sobytija mogut byt

* publicnymi
* semejnymi
* medicinskimi
* sistemnymi
* avtomaticeskimi
* rucnymi

kazdyj tip imeet svoi prava

**dlia kakoj celi eto delaetsia**

dlia celostnogo predstavleniia zizni

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et prosmotret istoriju po godam, ne smeshivaja publicnye fotografii s zakrytymi medicinskimi sobytijami

---

## 144 — vaznye zivotnye etapy

**cto eto dolzno delat**

mozno dobavit etap

* novyj dom
* adaptacija
* obuchenie
* vosstanovlenie
* sportsmenn yj period
* pozhiloj vozrast
* dlitelnoe lechenie
* perederzka
* podgotovka k adopcii

**pocemu eto nuzno**

odin i tot ze pokazatel moz et imet razn yj kontekst v raznye period y

**kak eto dolzno rabotat po logike**

etap imeet datu nacala, po zhelaniju datu zaversheniia, opisanie i auditoriju

**dlia kakoj celi eto delaetsia**

dlia kontekstnogo ponimaniia istorii

**kakoj rezultat dolzen byt dostignut**

izmenenie aktivnosti posle operacii ne sravnivaetsia bez konteksta s oby cnym aktivnym periodom

---

## 145 — spasatelnaja ili adopcionnaja istoria

**cto eto dolzno delat**

xoziajin ili priut moz et dobrovolno opisat

* otkuda pitomec
* kak byl najden
* kak prosla adaptacija
* kto pomog
* cto izmenilos
* kakie nuzny granicy privatnosti

**pocemu eto nuzno**

takaja istoria moz et byt vazna emocionalno i obrazovatelno, no moz et soderzhat cuvstitelnye dannye

**kak eto dolzno rabotat po logike**

nuzno ubrat

* adres predyduscego xoziajina
* lichnye konflikty
* nepodtverzhdennye obvinenija
* dokumenty tretix lic
* detali aktivnogo rassledovaniia

**dlia kakoj celi eto delaetsia**

dlia uvazitelnogo rasskaza bez vreda drugim

**kakoj rezultat dolzen byt dostignut**

istorija pitomca soxraniaetsia, no ne prevrashch a etsia v publicnoe obvinenie ili utecku dannyx

---

## 146 — den rozdenija ili den pojavljeniia doma

**cto eto dolzno delat**

profil moz et otm ec at

* tocn yj den rozdenija
* uslovnyj den rozdenija
* den adopcii
* den pojavljeniia doma
* den spaseniia

**pocemu eto nuzno**

dlia pitomca s neizvestnoj datoj rozdenija den doma moz et byt bolee realnym i vaznym

**kak eto dolzno rabotat po logike**

kazdaja data imeet pravilnoe nazvanie i ne vydajotsia za biologiceskuju datu rozdenija

**dlia kakoj celi eto delaetsia**

dlia chestnogo i emocionalno znachimogo prazdnika

**kakoj rezultat dolzen byt dostignut**

platforma ne sozdaet loznyj vozrast tolko radi ezhegodnogo uvedomleniia

---

## 147 — avtomaticeskie ezhegodnye napominanija

**cto eto dolzno delat**

xoziajin moz et polucat napominanie o

* dne rozdenija
* dne adopcii
* godovshine profilia
* vaznom dostizenii
* dni vozvrata posle propazhi

**pocemu eto nuzno**

eti sobytija emocionalno vazny, no ne dolzny obyazatelno publikovatsia

**kak eto dolzno rabotat po logike**

dlia kazdogo napominaniia mozno vybrat

* tolko lichnoe
* semejnoe
* podgotovit post
* ne napominat
* ne pokaz yvat reklamu

**dlia kakoj celi eto delaetsia**

dlia podderzki lichnoj istorii bez avtomaticeskogo spama

**kakoj rezultat dolzen byt dostignut**

xoziajin ne zabyvaet vaznuju datu, no profil ne publik uet pozdravlenie bez razresheniia

---

# rekomendacii i poisk druzej

## 148 — personalizirovannye rekomendacii na osnove profilia

**cto eto dolzno delat**

sistema moz et predlagat

* druzej
* gruppy
* sobytija
* mesta
* specialistov
* materialy
* tovary
* uslugi

**pocemu eto nuzno**

profil soderzit kontekst, kotoryj moz et sdelat rekomendacii poleznee

**kak eto dolzno rabotat po logike**

nuzno ispolzovat tolko razresennye dannye i objasniat

`pocemu eto rekomendovano`

naprimer

* tot ze vid
* poxozij temp progulki
* tot ze gorod
* obscaja gruppa
* podxodiascij vozrast
* interes k spokojnym progulkam

**dlia kakoj celi eto delaetsia**

dlia relev ant nogo otkrytiia novyx vozmoznostej

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet pon iatnye rekomendacii, a ne slucajn yj potok kontenta

---

## 149 — rekomendacija druzej ne garantiruet sovmestimost

**cto eto dolzno delat**

riadom s rekomendaciej dolzno byt objasnenie, cto eto tolko predlozhenie

**pocemu eto nuzno**

dva pitomca odnoj porody i vozrasta mogut byt nesovmestimy po povedeniju, zdorovju ili tempu

**kak eto dolzno rabotat po logike**

rekomendacija ne dolzna ispolzovat formulirovki

* idealnyj drug
* polnostiu sovmestimy
* bezopasno bez proverki
* garantirovanno podruzhatsia

**dlia kakoj celi eto delaetsia**

dlia predotvrashcheniia opasnyx ozhidanij

**kakoj rezultat dolzen byt dostignut**

xoziajin organizuet postepennoe i kontroliruemoe znakomstvo, a ne slepo doveriaet algoritmu

---

## 150 — faktory socialnoj sovmestimosti

**cto eto dolzno delat**

po zhelaniju mozno ucit yvat

* vid
* razmer
* vozrast
* uroven aktivnosti
* stil igry
* potre bnost v distancii
* reakciu na drugih zivotnyx
* mesto
* dostupnost
* opyt gruppovyx progulok
* osobye ogranicheniia

**pocemu eto nuzno**

odnogo sovpadeniia po porode nedostatochno

**kak eto dolzno rabotat po logike**

kazdyj faktor imeet ves tolko v ramkax bezopasnoj rekomendacii, a ne professionalnogo diagnoza

**dlia kakoj celi eto delaetsia**

dlia bolee podxodiascix pervyx kontaktov

**kakoj rezultat dolzen byt dostignut**

aktivnomu pitomcu ne rekomendujutsia tolko ocen medlennye kontakty, esli xoziaevam vazen obsch ij temp

---

## 151 — kontrol polzovatelia nad personalizaciej

**cto eto dolzno delat**

xoziajin moz et

* otkliucit rekomendacii druzej
* skryt konkretnyj profil
* ne ispolzovat lokaciju
* ne ispolzovat med icinskie statusy
* ubrat faktor
* ocistit istoriju rekomendacij
* posmotret osnovnye prichiny

**pocemu eto nuzno**

personalizacija ne dolzna byt skrytoj i neotkliucaemoj

**kak eto dolzno rabotat po logike**

otkaz ot personalizacii ne dolzen blokirovat bazovyj poisk i katalogi

**dlia kakoj celi eto delaetsia**

dlia kontrolia nad algoritmiceskim profilirovaniem

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et polzovatsia platformoj bez obyazatelnoj glubokoj personalizacii

---

# mnogoiazy cnost

## 152 — imia na raznyx alfavitax

**cto eto dolzno delat**

profil moz et xranit osnovnoe imia i alternativnye napisaniia

naprimer

* originalnoe
* latinskaja transliteracija
* mestnoe napisanie
* domashnee prozvisce

**pocemu eto nuzno**

imia moz et byt napisano po-raznomu v dokumentax, socialnom profile i pri poisk e

**kak eto dolzno rabotat po logike**

sistema ne dolzna avtomaticeski perevodit ili meniat imia bez podtverzhdeniia

**dlia kakoj celi eto delaetsia**

dlia poiska i korrektnogo otobrazheniia

**kakoj rezultat dolzen byt dostignut**

profil naxoditsia po raznym dopustimym napisaniiam, no osnovnoe imia ostajotsia pod kontrolem xoziajina

---

## 153 — perevod bio i opisanii

**cto eto dolzno delat**

publicnye teksty profilia mozno perevesti

* avtomaticeski
* rucno
* professionalno
* s pokazom originala

**pocemu eto nuzno**

mezhdunarodnoe soobscestvo dolzno ponimat bazovuju informaciju o pitomce

**kak eto dolzno rabotat po logike**

original vsegda ostajotsia dostupnym, a avtomaticeskij perevod imeet metku

**dlia kakoj celi eto delaetsia**

dlia mnogoiazy cnogo obseniia bez poteri originalnogo smysla

**kakoj rezultat dolzen byt dostignut**

polzovatel drugogo jazyka ponimaet opisanie, no moz et proverit original

---

## 154 — stabilnye spravocniki mezhdu jazykami

**cto eto dolzno delat**

vid, poroda, okras, xarakter i statusy dolzny imet stabilnye vnutrennie identifikatory i perevody

**pocemu eto nuzno**

esli kazdyj jazyk xranit otdelnyj tekst, poisk i filtry sozda dut dublikaty

**kak eto dolzno rabotat po logike**

v baze xranitsia odin identifikator, a v interfejse pokazyvaetsia perevod na tekuscem jazyke

**dlia kakoj celi eto delaetsia**

dlia edinoj logiki poisk a i analitiki

**kakoj rezultat dolzen byt dostignut**

labrador na raznyx jazykax ostajotsia odnoj porodoj v sisteme

---

## 155 — cto ne nado avtomaticeski perevodit

**cto eto dolzno delat**

bez podtverzhdeniia ne nado izmeniat

* imia
* username
* mikrochip
* registracionnye nomera
* nazvanie kliniki
* nazvanie preparata
* kod dokumenta
* nazvanie organizacii
* komandy pitomca

**pocemu eto nuzno**

oshibka perevoda takix dannyx moz et narushit identifikaciju ili bezopasnost

**kak eto dolzno rabotat po logike**

takie fragmenty oznachajutsia kak ne perevodimye ili trebujusc ie rucnogo perevoda

**dlia kakoj celi eto delaetsia**

dlia soxraneniia tocn yx identifikatorov

**kakoj rezultat dolzen byt dostignut**

perevod profilia ne meniaet imia lekarstva, mikrochip ili slova, na kotorye reagiruet pitomec

---

# dostupnost interfejsa

## 156 — dostupnost profilia dlia ekrannyx diktorov

**cto eto dolzno delat**

vse blok i profilia dolzny imet logiceskie zagolovki i opisaniia

**pocemu eto nuzno**

bez struktury polzovatel ne moz et ponimat, gde fotografii, xoziajin, xarakter i dejstviia

**kak eto dolzno rabotat po logike**

avatar imeet alternativnyj tekst, znachki imejut tekstovoe znachenie, a knopki ne nazyvajutsia prosto ikonka

**dlia kakoj celi eto delaetsia**

dlia samostojatelnogo prosmotra i upravleniia

**kakoj rezultat dolzen byt dostignut**

profil polnostiu pon iaten bez vizualnogo vosprijatiia

---

## 157 — upravlenie klaviaturoj

**cto eto dolzno delat**

mozno bez myshi

* prosmatrivat profil
* perekljucat vkladki
* redaktirovat
* zagruzhat media
* menjat privatnost
* upravliat xoziaevami
* prinimat zaprosy
* blokirovat
* otpravliat zhalobu

**pocemu eto nuzno**

eto vazno dlia dostupnosti i professionalnoj raboty na kompiutere

**kak eto dolzno rabotat po logike**

poriadok fokusa sootvetstvuet vizualn oj i logiceskoj strukture

**dlia kakoj celi eto delaetsia**

dlia polnocennogo upravleniia bez zavisimosti ot myshi

**kakoj rezultat dolzen byt dostignut**

ves zivotn yj cikl profilia dostup en s klaviatury

---

## 158 — ne tolko cvet

**cto eto dolzno delat**

statusy dolzny imet tekst i ikonku

* poterialsia
* ishchet dom
* podtverzhden
* trebuet proverki
* memorial
* sporn oe vladenie
* mikrochip est
* dostup ogranichen

**pocemu eto nuzno**

cvet moz et byt nevid en ili nepon iaten polzovateliam s narusheniem cvetovosprijatiia

**kak eto dolzno rabotat po logike**

odin status odinakovo oznachaetsia vo vseh razdelax

**dlia kakoj celi eto delaetsia**

dlia dostupnosti i posledovatelnosti

**kakoj rezultat dolzen byt dostignut**

vazn yj status pon iaten bez neobxodimosti razlicat cveta

---

## 159 — krupnyj tekst i mobilnaja adaptacija

**cto eto dolzno delat**

profil dolzen korrektno rabotat pri uvelichenii teksta i na malenkom ekrane

**pocemu eto nuzno**

dlinnye imena, porody, statusy i knopki mogut lomat interfejs

**kak eto dolzno rabotat po logike**

tekst perenositsia, knopki ostajutsia dostupnymi, a vaznaja informacija ne skryvaetsia za gorizontalnoj prokrutkoj

**dlia kakoj celi eto delaetsia**

dlia udobstva na telefone i pri ogranichennom zrenii

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et upravliat profilem bez slucajnyx nazhatij i obrezannogo teksta

---

# mobilnaja versija

## 160 — glavnyj mobilnyj ekran profilia

**cto eto dolzno delat**

na telefone srazu vidny

* avatar
* imia
* status
* osnovnoe bio
* xoziajin
* knopka napisat
* knopka druzhby
* knopka podelitsia
* knopka soobscit o nabludenii pri propazhe
* poslednie publikacii

**pocemu eto nuzno**

na malenkom ekrane nelzia srazu pokaz yvat vse razdely

**kak eto dolzno rabotat po logike**

kriticeskoe dejstvie meniaetsia po statusu

naprimer pri propazhe glavn oj knopkoj stanovitsia ja videl

**dlia kakoj celi eto delaetsia**

dlia bystrogo dostup a k samomu vaznomu

**kakoj rezultat dolzen byt dostignut**

polzovatel ne ishchet srocn uju funkciu v glubokom meniu

---

## 161 — bystroe redaktirovanie na telefone

**cto eto dolzno delat**

xoziajin moz et bystro izmenit

* avatar
* bio
* status
* fotografiju
* socialnuju dostupnost
* poslednee obnovlenie
* identifikacionnuju fotografiju

**pocemu eto nuzno**

mnogie fotografii i izmeneniia sozdajutsia imenno na telefone

**kak eto dolzno rabotat po logike**

kriticeskie polia, takie kak vladenie, chip i udaleniie, ne dolzny smeshivatsia s bystrym redaktirovaniem

**dlia kakoj celi eto delaetsia**

dlia udobstva bez snizeniia bezopasnosti

**kakoj rezultat dolzen byt dostignut**

oby cnoe obnovlenie delaetsia bystro, a opasnoe izmenenie trebuet otdelnogo processa

---

## 162 — kamera iz profilia

**cto eto dolzno delat**

mozno srazu

* sdelat avatar
* dobavit v galereju
* sozdat post
* obnovit identifikacionnuju fotografiju
* sdelat fotografiju osoboj primety
* sdelat dokumentalnoe foto

**pocemu eto nuzno**

odin i tot ze snimok moz et imet raznoe naznachenie i privatnost

**kak eto dolzno rabotat po logike**

pered soxraneniem polzovatel vybiraet tip, auditoriju i sviazann yj razdel

**dlia kakoj celi eto delaetsia**

dlia pravilnoj klassifikacii media

**kakoj rezultat dolzen byt dostignut**

fotografija shva ne pojavliaetsia slucajno v publicnoj socialnoj galeree

---

## 163 — oflajn dostup k bazovoj kartocke

**cto eto dolzno delat**

po soglasiju telefon moz et xranit zakrytuju oflajn kopiju

* fotografii
* imeni
* vida
* mikrochip statusa
* ekstrennogo kontakta
* bazovoj instrukcii
* qr koda

**pocemu eto nuzno**

internet moz et otsutstvovat v poezdke, lesu, klinike ili pri poisk e

**kak eto dolzno rabotat po logike**

oflajn kopija zasciscaetsia ustrojstvom i imeet datu poslednego obnovleniia

**dlia kakoj celi eto delaetsia**

dlia dostup a k kriticeskomu minimumu bez interneta

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et pokazat bazovuju kartocku v klinike daze pri otsutstvii seti

---

# desktop versija

## 164 — rasshirenn yj redaktor profilia

**cto eto dolzno delat**

na kompiutere mozno udobno upravliat

* vsemi razdelami
* albomami
* pravami
* dokumentami
* vremenn oj liniej
* dublikatami
* integracijami
* eksportom
* auditom

**pocemu eto nuzno**

sloznaja rabota s mnogoletnim profilem moz et byt neudobnoj na telefone

**kak eto dolzno rabotat po logike**

redaktor dolzen pokaz yvat nesoxranennye izmeneniia, konflikty i predprosmotr

**dlia kakoj celi eto delaetsia**

dlia professionalnogo i detalnogo upravleniia

**kakoj rezultat dolzen byt dostignut**

xoziajin ili priut moz et za odin ekran upravliat bolshim obemom dannyx bez poteri konteksta

---

## 165 — sravnenie profilej pri dublikate

**cto eto dolzno delat**

pri vozmoznom dublikate administrator vidit dve kartocki riadom

* fotografii
* imena
* vid
* vozrast
* xoziaev
* chip status
* dokumenty
* istoriju
* konflikty

**pocemu eto nuzno**

avtomaticeskoe obedinenie moz et slucajno soedinit dvux poxozix zivotnyx

**kak eto dolzno rabotat po logike**

sistema predlagaet, no ne vypolniaet neobratimoe obedinenie bez proverki

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo ispravleniia dublikatov

**kakoj rezultat dolzen byt dostignut**

dva cernyx labradora odnogo vozrasta ne obed injajutsia tolko po vneshnemu poxozestvu

---

# zivotn yj cikl i zavershenie profilia

## 166 — vrem ennoe skrytie profilia

**cto eto dolzno delat**

xoziajin moz et vrem enno ubrat profil iz publicnogo prosmotra

**pocemu eto nuzno**

moz et byt konflikt, ugroza, presledovanie, pauza v socialnoj aktivnosti ili zhelanie peresmotret privatnost

**kak eto dolzno rabotat po logike**

pri skrytii

* profil ischezaet iz poisk a
* rekomendacii ostanavlivajutsia
* novye zaprosy blokirujutsia
* medkartocka i dnevnik prodolzajut rabotat
* aktivnye professionalnye obyazatelstva obrabatyvajutsia otdelno

**dlia kakoj celi eto delaetsia**

dlia pauzy bez udaleniia vaznoj istorii

**kakoj rezultat dolzen byt dostignut**

pitomec moz et byt skryt iz socialnoj seti, no xoziajin ne ter iaet uxod, dokumenty i gps

---

## 167 — arhiv profilia

**cto eto dolzno delat**

profil moz et byt arhivirovan, esli on bolshe ne ispolzuetsia aktivno, no dolzen soxranitsia

**pocemu eto nuzno**

naprimer profil byl sozd an dublikatno, pitomec peredan organizacii ili socialnaia aktivnost zavershena

**kak eto dolzno rabotat po logike**

arhiv ne pojavliaetsia v rekomendacijax, no moz et byt dostup en upravliajuscim i pri neobxodimosti vosstanovlen

**dlia kakoj celi eto delaetsia**

dlia umensheniia shuma bez neobratimoj poteri

**kakoj rezultat dolzen byt dostignut**

neaktivnye profilej ne zasoriajut poisk, no vaznaia istoria ne unictozhaetsia

---

## 168 — udaleniie profilia pitomca

**cto eto dolzno delat**

osnovnoj xoziajin moz et zapustit udaleniie pri nalichii sootvetstvujusc ix prav

**pocemu eto nuzno**

profil moz et byt sozdan po oshibke ili polzovatel moz et xotet udal it ego dannye

**kak eto dolzno rabotat po logike**

pered udaleniem nuzno proverit

* sovladelcev
* medkartocku
* dokumenty
* aktivnyj poisk
* adopciju
* marketplace
* ustrojstva
* qr kody
* zakazy
* professionalnye zapisi
* publicnye posty
* memorialnye materialy
* zhaloby

**dlia kakoj celi eto delaetsia**

dlia osoznannogo udaleniia bez razruseniia aktivnyx processov

**kakoj rezultat dolzen byt dostignut**

profil ne moz et byt slucajno udal en vo vremia poisk a, spora, adopcii ili aktivnogo lechenija

---

## 169 — period otmeny udaleniia

**cto eto dolzno delat**

udaleniie moz et imet vrem ennyj period, v tecenie kotorogo ego mozno otmenit

**pocemu eto nuzno**

profil moz et byt udal en po oshibke, posle vzloma ili emocionalnogo resheniia

**kak eto dolzno rabotat po logike**

vo vremia perioda profil skryt, no ne unictozh en

vosstanovlenie trebuet proverki upolnomochennogo xoziajina

**dlia kakoj celi eto delaetsia**

dlia zascity ot neobratimoj poteri

**kakoj rezultat dolzen byt dostignut**

vzlomannyj akkaunt ne moz et nemedlenno unictozhit mnogoletnjuju istoriju pitomca

---

## 170 — cto proisxodit s postami posle udaleniia profilia

**cto eto dolzno delat**

platforma dolzna obrabotat

* posty
* kommentarii
* gruppovye obsuzhdeniia
* albomy
* otzyvy
* forum nye otvety
* sobytija
* adopcionnye istorii

**pocemu eto nuzno**

polnoe udaleniie kontenta moz et razrushit gruppovye obsuzhdeniia, no soxranenie sviazi s profil em moz et narushat privatnost

**kak eto dolzno rabotat po logike**

v zavisimosti ot tipa dannyx mozno

* udal it
* anonimnizirovat
* soxranit v gruppe bez aktivnoj ssylki
* peredat drugomu administratoru alboma
* skryt
* arhivirovat

**dlia kakoj celi eto delaetsia**

dlia balansa prav xoziajina i celostnosti soobscestva

**kakoj rezultat dolzen byt dostignut**

polzovatel zaranee znaet, kak obrabotajutsia raznye tipy kontenta

---

# memorialnyj rezhim

## 171 — aktivacija memorialnogo profilia

**cto eto dolzno delat**

posle smerti pitomca xoziajin moz et perevesti profil v memorialnyj rezhim

**pocemu eto nuzno**

polnoe udaleniie moz et byt slishkom boleznennym, a oby cn yj aktivnyj profil prodolzit pokaz yvat neumesnye napominanija

**kak eto dolzno rabotat po logike**

pered aktivaciej nuzno

* podtverdit reshenie
* vybrat publicnost
* otkliucit bytovye napominanija
* ostanovit rekomendacii uslug
* obrabotat ustrojstva
* soxranit medkartocku
* vybrat memorialnogo upravliajuscego
* nastroit kommentarii

**dlia kakoj celi eto delaetsia**

dlia uvazitelnogo soxraneniia pamiati

**kakoj rezultat dolzen byt dostignut**

profil ostajotsia mestom pamiati, a ne prodolzaet obrashchatsia s pitomcem kak s aktivnym polzovatelem

---

## 172 — cto otkliucaetsia v memorialnom rezhime

**cto eto dolzno delat**

nuzno ostanovit

* kormlenie
* lekarstva
* progulki
* vakcinacii
* gps trevozh nye signaly
* marketplace rekomendacii
* gruming
* sobytija
* poisk druzej
* avtomaticeskie pozdravleniia
* push o pokupke korma

**pocemu eto nuzno**

takie uvedomlenija mogut byt ocen boleznennymi i neumesnymi

**kak eto dolzno rabotat po logike**

upravliajusc ij moz et soxranit istoriju razdelov, no aktivnye napominanija zavershajutsia

**dlia kakoj celi eto delaetsia**

dlia emocionalno uvazitelnogo interfejsa

**kakoj rezultat dolzen byt dostignut**

semja ne polucaet avtomaticeskoe soobsenie pora kupit korm posle smerti pitomca

---

## 173 — memorialnye materialy

**cto eto dolzno delat**

profil moz et soderzhat

* fotografii
* video
* istoriju zizni
* vaznye daty
* blagodarnosti
* kommentarii druzej
* virtualn uju svechu ili znak pamiati
* lichnye zakrytye materialy
* publicnuju memorialnuju stranicu

**pocemu eto nuzno**

dlia mnogix xoziaev profil stanovitsia vaznoj castiu pamiati

**kak eto dolzno rabotat po logike**

xoziajin nast r aivaet, kto moz et kommentirovat i videt materialy

reklama i neumesnye rekomendacii ne pokazyvajutsia

**dlia kakoj celi eto delaetsia**

dlia spokojnoj cifrovoj pamiati

**kakoj rezultat dolzen byt dostignut**

profil podderzhivaet semju i druzej, a ne ispolzuet utratu dlia marketinga

---

## 174 — upravlenie memorialnym profilem posle utraty dostup a

**cto eto dolzno delat**

xoziajin moz et zaranie naznacit doverennogo upravliajuscego

**pocemu eto nuzno**

osnovnoj xoziajin moz et utratit dostup, stat nedostupnym ili sam ne moc upravliat profil em

**kak eto dolzno rabotat po logike**

doverenn yj upravliajusc ij polucaet tolko zaranee opredelennye prava posle proverennogo sobytija ili rucnogo podtverzhdeniia

**dlia kakoj celi eto delaetsia**

dlia dolgosrocnogo soxraneniia pamiati

**kakoj rezultat dolzen byt dostignut**

profil ne stanovitsia navsegda zabroshennym i ne ter iaetsia pri potere osnovnogo akkaunta

---

# moderacija i bezopasnost

## 175 — zhaloba na profil pitomca

**cto eto dolzno delat**

polzovatel moz et pozhalovatsia po prichinam

* falshiv yj profil
* ukradennye fotografii
* vydaca za cuzego pitomca
* zhestokoe obrashenie
* nelegalnaja prodaza
* poddelnaja adopcija
* rasskrytie lichnyx dannyx
* opasnyj kontent
* spam
* moshenniceskij sb or
* zivotnoe ne sushestvuet
* drugaja pricina

**pocemu eto nuzno**

profil moz et byt ispolzovan dlia obmana ili prikrytiia vreda

**kak eto dolzno rabotat po logike**

zhalobscik vybiraet konkretn yj kontent, opis yvaet problem u i po zhelaniju prilagaet dokazatelstva

**dlia kakoj celi eto delaetsia**

dlia strukturirovannoj proverki

**kakoj rezultat dolzen byt dostignut**

moderator polucaet kontekst, a avtor zhaloby ne raskryvaetsia upravliajuscemu profilem

---

## 176 — zhaloba na vladenie profilem

**cto eto dolzno delat**

mozno soobscit, cto profil

* upravliaetsia ne tem celovekom
* byl ukraden
* ne peredan posle adopcii
* soderzit chuzoj mikrochip
* sozd an po ukradennym dokumentam
* ispolzuetsia v spore

**pocemu eto nuzno**

cifrovoj profil moz et davat dostup k dokumentam, lokacii i socialnomu doveriju

**kak eto dolzno rabotat po logike**

do proverki mozno vrem enno zablokirovat

* peredacu
* udaleniie
* izmenenie chipa
* gps dostup
* marketplace
* adopciju

**dlia kakoj celi eto delaetsia**

dlia predotvrashcheniia dalnejshego zahvata

**kakoj rezultat dolzen byt dostignut**

sporn yj profil ne moz et byt bystro peredan tretjemu licu ili udal en vmeste s dokazatelstvami

---

## 177 — poddeln yj ili vymyslenn yj pitomec

**cto eto dolzno delat**

sistema dolzna raspoznavat akkaunty, sozda v a jusc ie mnogo falshiv yx profilej dlia

* spama
* nakrutki
* otzyvov
* golosovaniia
* marketplace
* sbora deneg
* obxoda blokirovki

**pocemu eto nuzno**

profil pitomca moz et stat instrumentom massovoj manipulacii

**kak eto dolzno rabotat po logike**

signaly

* odinakovye fotografii
* ukradennye media
* mnogie profilej bez istorii
* odinakovye opisaniia
* nevozmoznye kombinacii dannyx
* massovye dejstviia
* nesootvetstvie vladen iia

**dlia kakoj celi eto delaetsia**

dlia zascity doverija socialnoj seti

**kakoj rezultat dolzen byt dostignut**

falshivye profilej ne mogut massovo vliiat na konkursy, otzyvy i marketplace

---

## 178 — ukradennye fotografii

**cto eto dolzno delat**

realnyj xoziajin ili avtor media moz et otpravit zhalobu na nepravomernoe ispolzovanie

**pocemu eto nuzno**

m oshenniki c asto sozdaijut falshivye adopcionnye ili sbornye profilej iz cuzix fotografij

**kak eto dolzno rabotat po logike**

platforma moz et

* sravnit originaly
* proverit daty
* zaprosit novuju fotografiju s proverocnym kodom
* vrem enno skryt profil
* soxranit dokazatelstva
* dat apellaciju

**dlia kakoj celi eto delaetsia**

dlia zascity realnyx xoziaev i polzovatelej ot moshennicestva

**kakoj rezultat dolzen byt dostignut**

ukradennye media ne mogut dolgo ispolzovatsia dlia sbora deneg ili loznoj adopcii

---

## 179 — zhestokoe obrashenie v kontente profilia

**cto eto dolzno delat**

platforma dolzna prioritetno rassmatrivat

* fiziceskoe nasilie
* opasnoe nakazanie
* lishenie bazovyx potrebnostej
* namerennoe zapugivanie
* nebezopasnye eksperimenty
* organizaciju travli zivotnogo
* prodvizhenie zhestokix metodov

**pocemu eto nuzno**

profil ne dolzen romantizirovat ili monetizirovat vred zivotnomu

**kak eto dolzno rabotat po logike**

kontent moz et byt vrem enno skryt, dokazatelstva soxraneny, a slucaj peredan specialnoj moderacii

dokumentalnyj material dlia spaseniia moz et byt dostup en s preduprezhdeniem

**dlia kakoj celi eto delaetsia**

dlia zascity zivotnogo i soobscestva

**kakoj rezultat dolzen byt dostignut**

zhestokij kontent ne stanovitsia trendovym i ne prodolzaet prinosit avt oru auditoriju

---

## 180 — nelegalnye ili ogranichennye vidy

**cto eto dolzno delat**

profil ekzoticeskogo ili potencialno ogranichennogo zivotnogo moz et trebovat dopolnitelnuju proverku

**pocemu eto nuzno**

nekotorye vidy mogut byt oxraniaemymi, opasnymi, nelegalno vvezennymi ili trebu jut specialnyx dokumentov

**kak eto dolzno rabotat po logike**

platforma moz et zaprosit

* dokument proisxozdeniia
* razreshenie
* registraciju
* region
* organizaciju
* uslovija soderzaniia

publicnyj profil ne dolzen pomogat nelegalnoj torgovle

**dlia kakoj celi eto delaetsia**

dlia zascity zivotnyx, liudej i okruzajuscej sredy

**kakoj rezultat dolzen byt dostignut**

socialnaja set ne stanovitsia katalogom ekologiceskoj kontrabandy ili opasnyx zivotnyx bez kontrolia

---

## 181 — moderacionnye dejstviia

**cto eto dolzno delat**

moderator moz et

* skryt konkretn yj post
* skryt media
* ubrat lichnye dannye
* ogranichit publikacii
* vrem enno skryt profil
* zamorozit peredacu
* otzyvat znachok
* zaprosit dokazatelstva
* obed init dublikat
* vosstanovit oshibocno skryt yj profil
* peredat slucaj specialnoj komande

**pocemu eto nuzno**

polnoe udaleniie profilia ne vsegda javliaetsia pravilnym ili proporcionalnym dejstviem

**kak eto dolzno rabotat po logike**

dejstvie dolzno sootvetstvovat konkretnomu risku i imet osnovanie

**dlia kakoj celi eto delaetsia**

dlia tocn oj i spravedlivoj moderacii

**kakoj rezultat dolzen byt dostignut**

odin problemnyj post ne unictozhaet mnogoletnjuju bezopasnuju istoriju, esli net osnovaniia blokirovat ves profil

---

## 182 — objasnenie moderacionnogo resheniia

**cto eto dolzno delat**

upravliajusc ij dolzen polucit

* kakoi kontent
* kakoe pravilo
* kakoe dejstvie
* srok
* cto mozno ispravit
* mozno li podat apellaciju
* cto proizojdet s profilem

**pocemu eto nuzno**

neponiatnaja blokirovka sozdaet konflikt i ne pomogaet ispravit narushenie

**kak eto dolzno rabotat po logike**

konfidencialnye dokazatelstva i lichnost zhalobscika ne raskryvajutsia

**dlia kakoj celi eto delaetsia**

dlia prozracnosti bez vreda bezopasnosti

**kakoj rezultat dolzen byt dostignut**

upravliajusc ij ponimaet reshenie i moz et korrektno ego osparivat ili ispravit problem u

---

## 183 — apellacija

**cto eto dolzno delat**

xoziajin moz et osparivat

* blokirovku
* otzyv vladen iia
* udaleniie media
* otkaz v proverke
* obed inenie profilej
* status nelegalnogo vida
* zamorozku adopcii
* skrytie profilia

**pocemu eto nuzno**

algoritm ili moderator moz et oshibitsia

**kak eto dolzno rabotat po logike**

apellacija soderzit

* reshenie
* objasnenie
* dokumenty
* fotografii
* kontakt organizacii
* novye dokazatelstva
* nomer obrasheniia

**dlia kakoj celi eto delaetsia**

dlia spravedlivogo povtornogo rassmotreniia

**kakoj rezultat dolzen byt dostignut**

oshibocno zablokirovann yj realnyj profil moz et byt vosstanovlen s soxraneniem istorii

---

# audit i kachestvo dannyx

## 184 — istoria izmenenij profilia

**cto eto dolzno delat**

sistema dolzna xranit vaznye izmeneniia

* imia
* vid
* porodu
* pol
* datu rozdenija
* mikrochip
* xoziaev
* status
* privatnost
* adopciju
* memorialnyj rezhim
* peredacu
* obed inenie

**pocemu eto nuzno**

vaznye dannye ne dolzny tixo perepisyvatsia bez vozmoznosti ponimat, cto proizoslo

**kak eto dolzno rabotat po logike**

dlia kazdogo izmeneniia xranitsia

* staroe znachenie
* novoe
* avtor
* vremia
* prichina
* dokazatelstvo
* sposob izmeneniia

**dlia kakoj celi eto delaetsia**

dlia rassleduemosti i ispravleniia oshibok

**kakoj rezultat dolzen byt dostignut**

mozhno ustanovit, kto izmenil chip, xoziajina ili datu rozdenija

---

## 185 — istochnik kazdogo fakta

**cto eto dolzno delat**

vaznye polia mogut imet istochnik

* xoziajin
* priut
* klinika
* dokument
* chip registr
* geneticeskij test
* specialist
* avtomaticeskoe ustrojstvo
* predpolozhenie
* neizvestno

**pocemu eto nuzno**

odin i tot ze fakt moz et imet raznyj urov en doverija

**kak eto dolzno rabotat po logike**

istochnik xranitsia otdelno ot znacheniia i moz et meniatcia posle proverki

**dlia kakoj celi eto delaetsia**

dlia chestnogo ponimaniia kachestva dannyx

**kakoj rezultat dolzen byt dostignut**

polzovatel vidit, cto vozrast ocen en priutom, a mikrochip podtverzhden klinikoj

---

## 186 — protivorechivye dannye

**cto eto dolzno delat**

esli dva istochnika ukazali raznoe, sistema dolzna pokazat konflikt

naprimer

* raznye daty rozdenija
* raznyj pol
* raznye mikrochipy
* raznye porody
* raznye xoziajie
* raznye statusy sterilizacii

**pocemu eto nuzno**

tixaja zam ena moz et unictozhit pravilnuju istoriju

**kak eto dolzno rabotat po logike**

nuzno

* soxranit oba istochnika
* pokazat razlicija
* zaprosit proverku
* vybrat tekuscee osnovnoe znachenie
* ostavit istoriju resheniia

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo ispravleniia protivorechij

**kakoj rezultat dolzen byt dostignut**

odin oshibocnyj import ne perepis yvaet podtverzhdenn yj mikrochip ili datu rozdenija

---

## 187 — uroven zapolnennosti bez moralnoj ocenki

**cto eto dolzno delat**

profil moz et pokaz yvat poleznye sledujusc ie shagi

* dobavit aktualnuju fotografiju
* ukazat chip status
* nast roit privatnost
* dobavit rezervnogo xoziajina
* proverit kontakty
* dobavit ekstrennuju instrukciju

**pocemu eto nuzno**

procent zapolneniia moz et davit na polzovatelia i zastavliat sobirat lishnie dannye

**kak eto dolzno rabotat po logike**

pokazyvaetsia prakticeskaia polza, a ne abstraktn yj rejting

**dlia kakoj celi eto delaetsia**

dlia postepennogo ulucsheniia bez peregruzki

**kakoj rezultat dolzen byt dostignut**

xoziajin dobavliaet tolko te dannye, kotorye realno polezny emu i pitomcu

---

## 188 — rezervn yj xoziajin ili doverenn yj kontakt

**cto eto dolzno delat**

mozno naznacit celoveka, kotoryj pomozet pri

* utrate dostup a
* hospitalizacii xoziajina
* srocn oj poezdke
* propazhe pitomca
* neaktivnosti osnovnogo akkaunta
* ekstrenn oj situacii

**pocemu eto nuzno**

profil i uxod ne dolzny polnostiu zaviset ot odnogo akkaunta

**kak eto dolzno rabotat po logike**

doverenn yj kontakt ne polucaet poln yj dostup srazu

ego prava aktivirujutsia po pravilam, vremeni ili podtverzhdennomu zaprosu

**dlia kakoj celi eto delaetsia**

dlia ustojcivosti uxoda

**kakoj rezultat dolzen byt dostignut**

pri nedostupnosti xoziajina pitomec ne ostajotsia bez instrukcij i cifrovogo upravleniia

---

# texniceskaja logika bez programmnogo koda

## 189 — profil pitomca kak otdelnyj obekt

**cto eto dolzno delat**

u profilia dolzny byt

* unikalnyj identifikator
* tekuscee imia
* status
* vid
* osnovnaja privatnost
* osnovn yj avatar
* vladenie
* upravliajusc ie
* data sozdaniia
* istoria
* audit
* sviazannye moduli

**pocemu eto nuzno**

profil ne dolzen byt prosto naborom polej v akkaunte xoziajina

**kak eto dolzno rabotat po logike**

odin akkaunt moz et upravliat mnogimi profiljami, a odin profil moz et imet mnogix upravliajuscix

**dlia kakoj celi eto delaetsia**

dlia korrektnoj modeli sviazej

**kakoj rezultat dolzen byt dostignut**

peredaca profilia ne trebuet peredaci akkaunta xoziajina

---

## 190 — upravliajusc ij profilem kak otdelnaja sviaz

**cto eto dolzno delat**

kazdaia sviaz dolzna soderzhat

* polzovatelia
* profil pitomca
* rol
* prava
* status
* datu nacala
* datu zaversheniia
* kto priglasil
* dokazatelstvo
* audit

**pocemu eto nuzno**

prostoe pole owner_id ne podderzhit sovladelcev, priut, perederzku i vremennyj dostup

**kak eto dolzno rabotat po logike**

odin profil imeet neskolko sviazej, kazdaia proveriaetsia pri dejstvii

**dlia kakoj celi eto delaetsia**

dlia gibkogo i bezopasnogo vladen iia

**kakoj rezultat dolzen byt dostignut**

sovladelec, sitter i klinika imejut raznye prava, hotia rabotajut s odnim profilem

---

## 191 — pole profilia kak znachenie s istochnikom

**cto eto dolzno delat**

kriticeskoe pole moz et xranit

* znachenie
* tip tochnosti
* istochnik
* avtora
* datu
* status proverki
* privatnost
* predydusc ie versii

**pocemu eto nuzno**

prostoe pole breed ili birth_date ne pokaz yvaet, otkuda informacija i naskolko ona tocna

**kak eto dolzno rabotat po logike**

ne vse socialnye polia trebu jut slozn oj modeli, no identifikacionnye i juridiceskie dannye dolzny byt vers i onnymi

**dlia kakoj celi eto delaetsia**

dlia kachestva i rassleduemosti dannyx

**kakoj rezultat dolzen byt dostignut**

pri spore mozno ponimat, kto i na osnovanii cego ukazal konkretn yj fakt

---

## 192 — media kak otdelnye obekty

**cto eto dolzno delat**

kazdaja fotografija ili video dolzny imet

* vladeleca
* avtora
* profil
* tip
* datu
* privatnost
* alternativnyj tekst
* metadannye
* status moderacii
* soglasie
* sviazannye profilej
* versii

**pocemu eto nuzno**

odin fajl moz et ispolzovatsia v avatar e, albome, post e i objavlenii o propazhe

**kak eto dolzno rabotat po logike**

ne nuzno fiziceski dublir ov at fajl dlia kazdogo razdela, no kazdoe ispolzovanie imeet svoi prava

**dlia kakoj celi eto delaetsia**

dlia ekonomii, celostnosti i privatnosti

**kakoj rezultat dolzen byt dostignut**

udaleniie fotografii iz odnogo posta ne obyazatelno unictozhaet ejo zakrytuju kopiju v poiskovoj kartocke

---

## 193 — status profilia kak otdelnyj zivotn yj cikl

**cto eto dolzno delat**

kazdoe izmenenie statusa dolzno imet

* star yj status
* novyj
* avtora
* vremia
* prichinu
* avtomaticeskie posledstviia
* vozmoznost otmeny
* audit

**pocemu eto nuzno**

perehod v poterialsia, adopcija ili memorial meniaet mnogo funkcii srazu

**kak eto dolzno rabotat po logike**

status ne dolzen prosto menyat tekst, on dolzen zapuskat kontroliruem yj scenarij

**dlia kakoj celi eto delaetsia**

dlia posledovatelnosti vsej sistemy

**kakoj rezultat dolzen byt dostignut**

posle statusa vozvrashen domoj srocnye push i forma nabludenija avtomaticeski zakryvajutsia

---

## 194 — servernaia proverka prav

**cto eto dolzno delat**

kazdoe dejstvie s profilem dolzno proveriat prava na servere

**pocemu eto nuzno**

skrytaia knopka ne zasciscaet ot priamogo zaprosa, starogo kesa ili izmenennogo prilozheniia

**kak eto dolzno rabotat po logike**

proverka ucit yvaet

* polzovatelia
* rol
* profil
* konkretn oe dejstvie
* srok
* blokirovku
* status profilia
* status akkaunta
* sporn oe vladenie
* professionalnuju proverku

**dlia kakoj celi eto delaetsia**

dlia realnoj bezopasnosti

**kakoj rezultat dolzen byt dostignut**

otzyv prava nemedlenno blokiruet izmenenie daze v staroj otkrytoj vkladke

---

## 195 — idempotentnost kriticeskix dejstvij

**cto eto dolzno delat**

povtornoe nazhatie ne dolzno dva raza

* sozdat profil
* priniat peredacu
* dobavit sovladelca
* aktivirovat memorial
* zapustit udaleniie
* sozdat objavlenie o propazhe
* peredat vladenie

**pocemu eto nuzno**

medlennyj internet i povtornye zaprosy mogut sozdat protivorechivye dannye

**kak eto dolzno rabotat po logike**

kriticeskoe dejstvie polucaet unikalnyj identifikator i moz et byt obrabotano tolko odin raz

**dlia kakoj celi eto delaetsia**

dlia predotvrashcheniia dublikatov i dvojn yx peredac

**kakoj rezultat dolzen byt dostignut**

odin zapros polzovatelia sozdaet odin predskazuemyj rezultat

---

## 196 — invalidacija kesa posle izmeneniia privatnosti

**cto eto dolzno delat**

posle zakrytiia profilia nuzno obnovit

* publicnuju stranicu
* poisk
* rekomendacii
* l ent u
* predprosmotr ssylok
* qr
* api
* vneshnju indeksaciju
* lokalnye keshi
* prava media

**pocemu eto nuzno**

novaja nast roika bespolezna, esli st araia kopija esio dostupna

**kak eto dolzno rabotat po logike**

kriticeskie izmeneniia privatnosti dolzny imet vysokij prioritet obnovleniia

**dlia kakoj celi eto delaetsia**

dlia fakticeskogo primeneniia nast roek

**kakoj rezultat dolzen byt dostignut**

skrytyj profil ne prodolzaet pokaz yvatsia v rekomendacijax i po star ym api otvetam

---

## 197 — poiskovyj indeks

**cto eto dolzno delat**

poisk dolzen indeksirovat tolko razresennye polia

**pocemu eto nuzno**

skrytoe pole moz et slucajno raskrytsia cerez rezultat poisk a ili podskazku

**kak eto dolzno rabotat po logike**

pri izmenenii privatnosti ili statusa indeks obnovliaetsia, a st ar y e rezultaty ne dolzny otkryvat zakryt yj profil

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo globalnogo poisk a

**kakoj rezultat dolzen byt dostignut**

poisk po staromu imeni ne raskryvaet profil, esli xoziajin ubral ego iz obnaruzheniia

---

## 198 — schetciki

**cto eto dolzno delat**

kolichestvo

* podpiscikov
* druzej
* postov
* media
* sobytij
* grupp
* dostizenij

dolzno byt odinakovym vo vseh razdelax

**pocemu eto nuzno**

raznye cifry snizajut doverie i ukazyvajut na oshibki dostup a ili kesa

**kak eto dolzno rabotat po logike**

schetciki dolzny ucit yvat privatnost, udalenn yj kontent, blokirovki i status zritel ia

**dlia kakoj celi eto delaetsia**

dlia posledovatelnosti interfejsa

**kakoj rezultat dolzen byt dostignut**

xoziajin i posetitel vidat korrektnye cifry v ramkax svoix prav

---

# minimalnaja versija dlia pervogo zapuska

## 199 — obyazatelnye funkcii pervoj versii

**cto eto dolzno delat**

pervaja stabilnaja versija dolzna vkliucat

* sozdanie minimalnogo profilia
* imia
* vid
* porodu ili metis
* pol
* datu ili primernyj vozrast
* avatar
* galereju
* bio
* xarakter
* interesy
* publicnuju lokaciju
* osnovnogo xoziajina
* sovladelcev
* individualnye prava
* bazovuju privatnost
* publicnyj url
* qr kod
* posty
* podpiski
* druzhbu mezhdu pitomcami
* blokirovku
* zhaloby
* status poterialsia
* status ishchet dom
* peredacu profilia
* vrem ennoe skrytie
* udaleniie
* memorialnyj rezhim
* audit kriticeskix izmenenij

**pocemu eto nuzno**

eto minimalnyj nabor, na kotorom mogut stabilno rabotat ostalnye socialnye funkcii

**kak eto dolzno rabotat po logike**

snachala nuzno obespechit stabiln yj zivotn yj cikl i prava, a potom dobavliat sloznye sertifikaty, ai i mezhsistemnye integracii

**dlia kakoj celi eto delaetsia**

dlia sozdaniia nadezhnogo fundamenta vsego proekta

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et polnostiu sozdat, pokazat, zakryt, peredat i arhivirovat profil bez obrasheniia k administratoru

---

## 200 — cto mozno dobavit posle stabilizacii

**cto eto dolzno delat**

na sledujusc ix etapax mozno dobavit

* proverku mikrochipa
* integraciju s registrami
* cifrovoj pasport
* professionalnye znachki
* poln uju rodoslovnuju
* geneticeskie dannye
* avtomaticeskoe sopostavlenie dublikatov
* ai opisanie fotografij
* semanticeskij poisk
* slozn yj profil sredy obitaniia
* nasledovanie profilia
* podderzhku municipalnyx organizacij
* peredacu mezhdu platformami
* professionalnye eksporty
* poln uju sistemu rodstvennyx sviazej
* podtverzhdenie sluzebnogo statusa

**pocemu eto nuzno**

eti funkcii polezny, no oni trebujut nadezhnogo bazovogo profilia, prav i audita

**kak eto dolzno rabotat po logike**

kazdaia novaia integracija dolzna podkliucatsia k odnomu profiliu i ne sozdaivat alternativnuju identichnost pitomca

**dlia kakoj celi eto delaetsia**

dlia rasshiri aemogo rosta bez razrusheniia osnovy

**kakoj rezultat dolzen byt dostignut**

profil moz et razv itsia ot prostoj socialnoj kartocki do polnogo cifrovogo pasporta

---

# pokazateli kachestva i dostizeniia rezultata

## 201 — uspeshnost sozdaniia profilia

**cto eto dolzno delat**

komanda dolzna izmeriat

* skolko polzovatelej nacali
* skolko sozdali minimalnyj profil
* gde otkazalis
* skolko sozdaetsia dublikatov
* skolko profilej ostajutsia cernovikami
* skolko profilej polucajut pravilnuju privatnost

**pocemu eto nuzno**

nalichie formy ne oznachaet, cto ona pon iatna i rabotaet v realnosti

**kak eto dolzno rabotat po logike**

analitika dolzna byt agregirovannoj i ne prosmatrivat lichnye opisaniia

**dlia kakoj celi eto delaetsia**

dlia ulucsheniia processa

**kakoj rezultat dolzen byt dostignut**

bolshinstvo xoziaev sozdaet rabochij profil bez podderzhki i bez loznyx polej

---

## 202 — dolia dublikatov

**cto eto dolzno delat**

platforma dolzna otslezhivat

* novye vozmoznye dublikaty
* uspeshnye obed ineniia
* oshibocnye sovpadeniia
* profilej odnogo pitomca u raznyx xoziaev
* dublikat y iz klinik i priutov

**pocemu eto nuzno**

dublikaty razrushajut medkartocku, socialnuju istoriju i poisk poterjannyx

**kak eto dolzno rabotat po logike**

algoritm predlagaet sovpadenie, no kriticeskoe obedinenie proveriaetsia celovekom

**dlia kakoj celi eto delaetsia**

dlia soxraneniia principa odin pitomec odin profil

**kakoj rezultat dolzen byt dostignut**

kolichestvo razdelennyx istorij snizaetsia, a oshibocnye obed ineniia ostajutsia redkimi i obratimymi

---

## 203 — bezopasnost peredaci

**cto eto dolzno delat**

nuzno izmeriat

* skolko peredac zaversheno
* skolko otmeneno
* skolko sporov
* skolko popytok bez dokazatelstv
* skolko staryx xoziaev soxranili lishn ij dostup
* skolko profilej ostalis bez upravliajuscego

**pocemu eto nuzno**

peredaca javliaetsia odn oj iz samyx riskov yx funkcij

**kak eto dolzno rabotat po logike**

problemy dolzny analizirovatsia bez raskrytiia lichnyx dokumentov v obschej analitike

**dlia kakoj celi eto delaetsia**

dlia ulucsheniia zascity pitomca i xoziaev

**kakoj rezultat dolzen byt dostignut**

peredaca stanovitsia predskazuemoj, a profilej bez realnogo upravliajuscego prakticeski ne ostajotsia

---

## 204 — pokazateli privatnosti

**cto eto dolzno delat**

nuzno otslezhivat

* slucai raskrytiia adresa
* slucai publicacii polnogo chipa
* zhaloby na media
* skrytye profili v poisk e
* vremia invalidacii kesa
* ispolzovanie predprosmotra privatnosti
* nast roiki po umolcaniju
* slucai nezhelatelnyx kontaktov

**pocemu eto nuzno**

formalnye nast roiki ne garantirujut realnuju zascitu

**kak eto dolzno rabotat po logike**

analitika dolzna byt agregirovannoj i napravlennoj na ispravlenie arhitektury, a ne na dopolnitelnoe profilirovanie xoziaev

**dlia kakoj celi eto delaetsia**

dlia snizeniia fiziceskix i cifrovyx riskov

**kakoj rezultat dolzen byt dostignut**

adresa, gps, telefon y i dokumenty ne pojavljajutsia publicno iz-za nepon iatnyx nast roek

---

## 205 — kachestvo dannyx profilia

**cto eto dolzno delat**

mozno ocenivat agregirovanno

* nalichie aktualnoj fotografii
* urov en tochnosti vozrasta
* istochnik porody
* podtverzhdenie mikrochipa
* nalichie rezervnogo upravliajuscego
* konflikty dannyx
* aktualnost statusa
* zavershennost peredaci

**pocemu eto nuzno**

profilej mogut vizualno vygl iadet polnymi, no soderzhat st ar y e ili protivorechivye fakty

**kak eto dolzno rabotat po logike**

platforma dolzna predlagat konkretnye ispravleniia, a ne moraln yj rejting xoziajina

**dlia kakoj celi eto delaetsia**

dlia podderzki aktualnoj cifrovoj lichnosti

**kakoj rezultat dolzen byt dostignut**

vaznye identifikacionnye dannye ostajutsia aktualnymi i mogut byt ispolzovany pri srocn oj situacii

---

# idealnye scenarii

## 206 — idealnyj scenarij sozdaniia oby cnogo profilia

**cto eto dolzno delat**

andrej dobavliaet baksa, ukazyvaet imia, vid, primernyj vozrast, fotografiju i svoj status osnovnogo xoziajina

**pocemu eto nuzno**

eto bazovyj scenarij bolshinstva novyx polzovatelej

**kak eto dolzno rabotat po logike**

sistema sozdaet zakryt yj po umolcaniju cernovik i predlagaet

* dobavit bio
* ukazat gorod bez adresa
* nast roit soobsenija
* dobavit sovladelca
* ukazat chip status
* proverit predprosmotr

**dlia kakoj celi eto delaetsia**

dlia bystrogo zapuska bez slucajnoj publicacii

**kakoj rezultat dolzen byt dostignut**

baks polucaet rabochij profil, no telefon, email, domashnij adres i medkartocka ostajutsia zakrytymi

---

## 207 — idealnyj scenarij neskolkix pitomcev

**cto eto dolzno delat**

andrej dobavliaet baksa i lunu kak otdelnye profilej

**pocemu eto nuzno**

u kazdogo svoi fotografii, xarakter, dnevnik, medkartocka i ustrojstva

**kak eto dolzno rabotat po logike**

semejnyj ekran pokaz yvaet oboix, no socialnye i medicinskie dannye ne smeshivajutsia

**dlia kakoj celi eto delaetsia**

dlia individualnogo upravleniia

**kakoj rezultat dolzen byt dostignut**

lekarstvo luny nelzia otmetit kak dano baksu, a gps baksa ne pokaz yvaetsia v profile luny

---

## 208 — idealnyj scenarij sovladelca

**cto eto dolzno delat**

andrej priglashaet drugogo clena semji upravliat baksom

**pocemu eto nuzno**

obsch ij parol byl by nebezopasen i ne pokaz yval by realnogo avtora dejstvij

**kak eto dolzno rabotat po logike**

sovladelec prinimaet priglashenie, polucaet dostup k dnevnik u, postam i poisk u pri propazhe, no ne moz et peredavat vladenie bez dopolnitelnogo soglasiia

**dlia kakoj celi eto delaetsia**

dlia sovmestnogo uxoda s individualnoj otvetstvennostiu

**kakoj rezultat dolzen byt dostignut**

oba upravljajut odnim profilem, a audit pokaz yvaet realnogo avtora kazdogo dejstviia

---

## 209 — idealnyj scenarij pitomca iz priuta

**cto eto dolzno delat**

priut sozdaet profil luny, dobavliaet istoriju, fotografii, vakcinacii, xarakter i adopcionnye uslovija

**pocemu eto nuzno**

luna esio ne imeet postojannogo xoziajina, no u nee uze est polnocennaja istoria

**kak eto dolzno rabotat po logike**

profil prinadlezit organizacii, a perederzka moz et dobavliat dnevnye nabludenija bez prava samostojatelno zaversit adopciju

**dlia kakoj celi eto delaetsia**

dlia koordinacii priuta i perederzki

**kakoj rezultat dolzen byt dostignut**

posle adopcii tot ze profil peredajotsia novomu xoziajinu vmeste s poleznoj istoriej

---

## 210 — idealnyj scenarij adopcii

**cto eto dolzno delat**

novyj xoziajin polucaet priglashenie prin iat profil luny

**pocemu eto nuzno**

soz d anie novogo profilia poteria lo by medicinskuju i socialnuju istoriju

**kak eto dolzno rabotat po logike**

pered peredacej pokaz yvaetsia

* cto peredajotsia
* cto ostanetsia zakrytym u priuta
* kak obnovit chip
* kakie aktivnye lekarstva
* kakie kontrolnye daty
* kakie prava ostanutsia u priuta po soglasiju

**dlia kakoj celi eto delaetsia**

dlia nepreryvnosti uxoda

**kakoj rezultat dolzen byt dostignut**

luna perehodit v novyj dom bez poteri vakcinacij, fotografij i instrukcij po adaptacii

---

## 211 — idealnyj scenarij najdennoj koski

**cto eto dolzno delat**

polzovatel naxodit kosku i sozdaet vremenn yj profil

**pocemu eto nuzno**

xoziajin esio neizvesten, no nuzno koordinirivat kliniku, chip i objavleniia

**kak eto dolzno rabotat po logike**

profil imeet status

`najdena, vladenie ne podtverzhdeno`

publicno pokaz yvaetsia primernyj rajon, no ne adres nashedshego

**dlia kakoj celi eto delaetsia**

dlia poisk a xoziajina bez prisvoeniia

**kakoj rezultat dolzen byt dostignut**

posle proverki chipa profil obed iniaetsia s sushestvujuscim profilem koski, a vremennaja kartocka ne ostajotsia dublikatom

---

## 212 — idealnyj scenarij dublikata

**cto eto dolzno delat**

drugoj clen semji pyt a etsia sozdat novyj profil baksa

**pocemu eto nuzno**

on moz et ne znat, cto profil uze sozdan andreem

**kak eto dolzno rabotat po logike**

sistema naxodit vozmozhnoe sovpadenie i predlagaet otpravit zapros na sovladenie

**dlia kakoj celi eto delaetsia**

dlia soxraneniia odnogo profilia

**kakoj rezultat dolzen byt dostignut**

ne sozdaetsia vtoraja medkartocka i vtoroj spisok druzej

---

## 213 — idealnyj scenarij izmeneniia imeni

**cto eto dolzno delat**

posle adopcii lunu pereimenovyvajut

**pocemu eto nuzno**

novoe imia stanovitsia osnovnym, no staroe vazno dlia dokumentov i poisk a

**kak eto dolzno rabotat po logike**

staroe imia soxraniaetsia kak alternativnoe, ssylka profilia ne meniaetsia, a publicnaja vidimost starogo imeni nast r aivaetsia

**dlia kakoj celi eto delaetsia**

dlia gibkosti bez poteri identifikacii

**kakoj rezultat dolzen byt dostignut**

profil naxoditsia po staromu imeni iz priuta, no v socialnoj seti pokaz yvaetsia novoe

---

## 214 — idealnyj scenarij propazhi

**cto eto dolzno delat**

baks propadaet vo vremia progulki

**pocemu eto nuzno**

v srocn oj situacii vazna kazdaia minuta

**kak eto dolzno rabotat po logike**

iz profilia avtomaticeski podstavliajutsia

* aktualnaia fotografija
* okras
* osobye primety
* shlejka
* chip status
* straxi
* instrukcija ne presledovat
* kontakt cerez platformu

andrej dobavliaet poslednjuju tochku i publik uet

**dlia kakoj celi eto delaetsia**

dlia bystrogo zapuska poisk a

**kakoj rezultat dolzen byt dostignut**

kachestvennoe objavlenie pojavljaetsia za neskolko minut bez publicacii domashnego adresa

---

## 215 — idealnyj scenarij spornogo vladen iia

**cto eto dolzno delat**

dva polzovatelia utverzhdajut, cto odin i tot ze pitomec prinadlezit im

**pocemu eto nuzno**

odin moz et imet star yj chip, drugoj novyj dogovor peredaci

**kak eto dolzno rabotat po logike**

profil polucaet zascischenn yj status spornogo vladen iia

blokirujutsia

* peredaca
* udaleniie
* izmenenie chip a
* publicnye obvineniia
* adopcija
* marketplace

bazovyj uxod prodolzaetsia

**dlia kakoj celi eto delaetsia**

dlia zascity pitomca i dokazatelstv

**kakoj rezultat dolzen byt dostignut**

ni odna storona ne moz et unictozhit profil ili peredat ego tretjemu licu do proverki

---

## 216 — idealnyj scenarij professionalnogo profilia pitomca

**cto eto dolzno delat**

u sluzebnoj sobaki est oby cnyj socialnyj profil i otdelnyj proverenn yj status rabochego zivotnogo

**pocemu eto nuzno**

socialnye fotografii i oficialnaja kvalifikacija ne dolzny smeshivatsia

**kak eto dolzno rabotat po logike**

publicno pokazyvaetsia tip statusa, organizacija, region i cto provereno

dokumenty xran iatsia zakryto

**dlia kakoj celi eto delaetsia**

dlia chestnogo professionalnogo predstavleniia

**kakoj rezultat dolzen byt dostignut**

polzovateli vidat realno podtverzhdenn yj status, a ne samostojatelno dobavlenn yj dekorativnyj znachok

---

## 217 — idealnyj scenarij sittera

**cto eto dolzno delat**

andrej vyd ajet sitteru vrem ennyj dostup k profilem baksa na vyxodnye

**pocemu eto nuzno**

sitteru nuzny instrukcii, no ne poln yj kontrol profilia

**kak eto dolzno rabotat po logike**

sitter moz et

* videt bazovuju kartocku
* otmecat uxod
* zagruzhat otchet
* videt ekstrenn yj kontakt
* polzovatsia gps tolko vo vremia progulki

on ne moz et

* menjat xoziaev
* publikovat publicno bez prava
* videt vse dokumenty
* peredavat profil
* udal iat istoriju

**dlia kakoj celi eto delaetsia**

dlia bezopasnogo vremennogo uxoda

**kakoj rezultat dolzen byt dostignut**

posle vyxodnyx ego dostup avtomaticeski istekaet

---

## 218 — idealnyj scenarij zakrytogo profilia

**cto eto dolzno delat**

xoziajin ne xocet, ctob profil koski byl v globalnom poisk e

**pocemu eto nuzno**

emu nuzny medkartocka, dnevnik i ustrojstva, no ne nuzna socialnaja publicnost

**kak eto dolzno rabotat po logike**

profil zakryvaetsia ot rekomendacij, poisk a i vneshnej indeksacii

on ostajotsia dostupnym semejnomu krugu i po zascischenn oj ssylke specialistu

**dlia kakoj celi eto delaetsia**

dlia polnocennogo ispolzovaniia prakticeskix funkcii bez socialnoj otkrytosti

**kakoj rezultat dolzen byt dostignut**

socialnaja set ne zastavliaet kazdogo pitomca byt publicnym

---

## 219 — idealnyj scenarij memorialnogo profilia

**cto eto dolzno delat**

posle smerti baksa andrej perevodit profil v memorialnyj rezhim

**pocemu eto nuzno**

udal it mnogole tnjuju istoriju on ne xocet, no oby cnye napominanija boleznenny

**kak eto dolzno rabotat po logike**

sistema

* otkliucaet kormlenie
* otkliucaet vakcinacii
* otkliucaet gps signal y
* ne rekomenduet tovary
* soxraniaet fotografii
* arhiviruet medkartocku
* predlagaet eksport
* blokiruet neumesnuju reklamu

**dlia kakoj celi eto delaetsia**

dlia uvazitelnogo soxraneniia pamiati

**kakoj rezultat dolzen byt dostignut**

profil stanovitsia spokojnoj memorialnoj stranicej, a ne ostajotsia v oby cnom kommerceskom rezhime

---

# itogovyj rezultat punkta 2

posle polnoj realizacii punkta 2 profil pitomca dolzen stat ne prostoj stranic ej s fotografiej i imenem, a polnocennoj cifrovoj lichnostiu konkretnogo zivotnogo

v rezultate sistema dolzna obespecit

* odin osnovnoj profil na odnogo realnogo pitomca
* podderzku raznyx vidov zivotnyx
* bystroe minimalnoe sozdanie
* postepennoe rasshirenie bez obyazatelnoj ogromnoj ankety
* tocn oe razdelenie xoziajina, sovladelca, sittera, priuta i specialista
* individualnye prava kazdogo upravliajuscego
* stabiln uju ssylku i qr kod
* aktualnye identifikacionnye fotografii
* publicnyj socialnyj profil bez utecki med icinskix dannyx
* xarakter, interesy, straxi i bezopasnye instrukcii
* druzhbu mezhdu pitomcami
* publikacii, albomy, gruppy i sobytija
* integraciju s medkartockoj, dnevnikom, gps, marketplace i ekspertami
* bezopasnuju adopciju i peredacu novomu xoziajinu
* vremenn yj dostup sittera i perederzki
* zascitu ot dublikatov i prisvoeniia
* sporn yj rezhim vladen iia
* audit vseh vaznyx izmenenij
* individualnuju privatnost kazdogo razdela
* memorialnyj rezhim bez neumesnyx napominanij
* mnogoiazy cnost i dostupnost
* bezopasnoe udaleniie, arhiv i eksport

glavn oe dostizenie etogo punkta zakliucaetsia v tom, cto vse ostalnye moduli socialnoj seti budut rabotat s odnim stabilnym, proveriaemym i upravljaemym profilem realnogo pitomca, a ne s naborom nesviazannyx fotografij, postov i dokumentov
</pet-profile-source-revision>

## Revision 2026-07-31: Social Relationships And Safe Introductions

This dated revision is additive. Parts A and B and the pet-profile revision
above remain unchanged and mandatory. The revision payload below is preserved
verbatim from local Codex history and is part of the indivisible master
specification.

- Social revision source timestamp: `1785521058`
- Social revision raw payload SHA-256: `5455fc185c1348ac7233d35ec18285b850c19e0bb28cbda2dc90eeb87bc6276d`
- Current master raw payload SHA-256: `ad88d55de0faf7d5fe62c97479be42f6539316a13eeae9d2bbfd8a6b3716c32d`
- Current master checksum payload: prior master checksum payload, two LF characters, exact social revision payload

<social-relationships-source-revision>
# punkt 3 — socialnye sviazi, druzhba mezhdu pitomcami i xoziaevami, podpiski, zaprosy, blizkij krug, rekomendacii i bezopasnye znakomstva

## 1 — glavnaja cel vsego punkta

**cto eto dolzno delat**

sistema socialnyx sviazej dolzna pozvolit xoziaevam i pitomcam bezopasno naxodit drug druga, podpisyvatsia na interesnye profilej, sozdavat dvustoronnju druzhbu, formirovat blizkij krug, dogovarivatsia o progulkax, ucavstvovat v gruppax i podderzhivat dolgosrocnye socialnye kontakty

**pocemu eto nuzno**

socialnaja set ne moz et sosto iat tolko iz otdelnyx profilej i postov

glavnaja cennost pojavliaetsia togda, kogda polzovateli mogut naxodit podxodiascix liudej, sozdavat doveritelnye sviazi, obmenivatsia opytom i organizovyvat realnye ili onlajn aktivnosti

**kak eto dolzno rabotat po logike**

sistema dolzna razdeliat raznye tipy sviazej

* odnostoronnjaia podpiska
* dvustoronnjaia druzhba xoziaev
* dvustoronnjaia druzhba pitomcev
* semejnaja sviaz
* sovladenie
* professionalnaja sviaz
* sviaz cerez gruppu
* sviaz cerez sobytie
* vrem ennyj kontakt
* blizkij krug
* blokirovka
* ogranichenie
* skrytie kontenta

kazdyj tip dolzen imet otdelnye prava, nastojki i pon iatnye posledstviia

**dlia kogo i dlia kakoj celi**

funkcija rabotaet dlia xoziaev, buduscix xoziaev, volonterov, specialistov, priutov, sitterov, organizatorov sobytij i semejnyx grupp

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et sozdavat nuznye socialnye sviazi bez publicacii telefona, adresa, tocn oj lokacii, medkartocki ili drugix cuvstitelnyx dannyx

---

# osnovnaja model socialnyx sviazej

## 2 — odna sviaz ne dolzna oznachat vse

**cto eto dolzno delat**

platforma dolzna razdeliat pon iatiia podpiska, druzhba, sovladenie, semejnyj dostup, professionalnaja sviaz i uchastie v gruppe

**pocemu eto nuzno**

esli odna knopka dobavit v druzia avtomaticeski otkryvaet lichnye posty, status v seti, gps, semejnye fotografii i profil pitomca, eto sozdaet seriozn yj risk privatnosti

**kak eto dolzno rabotat po logike**

kazdaia sviaz dolzna otvechat tolko za konkretn yj socialnyj kontekst

podpiska daet dostup k publicnym obnovlenijam

druzhba moz et davat dostup k kontentu dlia druzej

sovladenie daet otdelnye prava upravleniia pitomcem

professionalnaja sviaz dejstvuet tolko v ramkax konsultacii ili uslugi

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej, kotorym nuzno ponimat, cto imenno izmenitsia posle prin iatiia zaprosa

**kakoj rezultat dolzen byt dostignut**

odin socialnyj kontakt ne polucaet avtomaticeski lishnie prava na dokumenty, lokaciju, ustrojstva ili upravlenie pitomcem

---

## 3 — razdelenie sviazej mezhdu liudmi i pitomcami

**cto eto dolzno delat**

sistema dolzna razdeliat

* druzhbu mezhdu xoziaevami
* druzhbu mezhdu pitomcami
* podpis ku na xoziajina
* podpis ku na pitomca
* socialnuju sviaz mezhdu semejnymi profiljami
* professionaln yj kontakt

**pocemu eto nuzno**

dva pitomca mogut reguliarno igrat vmeste, no ix xoziajie ne obyazatelno xotiat pokaz yvat drug drugu lichnye publikacii

i naoborot, dva xoziajina mogut byt druzjami, hotia ix pitomcy ne kontaktirujut

**kak eto dolzno rabotat po logike**

kazdaia sviaz sozdaetsia otdelno

pri dobavlenii druzhby pitomcev platforma ne dolzna avtomaticeski delat xoziaev druzjami

ona moz et predlozhit otdelnyj zapros, no ne vypolniat ego bez soglasiia

**dlia kogo i dlia kakoj celi**

dlia sem ej s neskolkimi pitomcami, mestnyx grupp progulok, sitterov i aktivnyx socialnyx profilej

**kakoj rezultat dolzen byt dostignut**

socialn yj graf otobrazaet realnye otnosheniia, a ne odnu obschuju nepon iatnuju sviaz

---

## 4 — odnostoronnjaia podpiska

**cto eto dolzno delat**

polzovatel moz et podpisatsia na publicn yj profil xoziajina, pitomca, specialista, priuta ili organizacii

**pocemu eto nuzno**

podpiska pozvoliaet sledit za interesnym kontentom bez obyazatelnoj dvustoronnei druzhby

**kak eto dolzno rabotat po logike**

podpiska moz et byt

* svobodnoj dlia publicnogo profilia
* trebu juscej odobreniia dlia zakrytogo profilia
* vrem enno priostanovlennoj
* udal ennoj vladelcem profilia
* ogranichennoj tolko vybrannymi tipami publikacij

**dlia kogo i dlia kakoj celi**

dlia podpiscikov populiarnyx pitomcev, priutov, ekspertov, blogerov, organizacij i mestnyx soobscestv

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet interesnye obnovleniia, no ne stanovitsia lichnym drugom i ne polucaet dostup k zakrytym dannym

---

## 5 — dvustoronnjaia druzhba xoziaev

**cto eto dolzno delat**

dva xoziajina mogut podtverdit vzaimnuju socialnuju sviaz

**pocemu eto nuzno**

dvustoronnjaia druzhba podxodit dlia ljudej, kotorye realno znakom y, reguliarno obschajutsia ili xotiat delitsia zakrytym kontentom

**kak eto dolzno rabotat po logike**

odin polzovatel otpravliaet zapros, vtoroj prinimaet, otkloniaet, ogranichivaet ili ostavliaet ego bez otveta

posle prin iatiia kazdaia storona vse ravno soxraniaet individualnye nastojki privatnosti

**dlia kogo i dlia kakoj celi**

dlia xoziaev, volonterov, sosed ej, uchastnikov grupp i liudej, kotorye obschajutsia vne odnogo sobytija

**kakoj rezultat dolzen byt dostignut**

druzia mogut videt razresenn yj kontent i proshche sviazyvatsia, no ni odna storona ne polucaet avtomaticeski adres, telefon ili med icinskie dannye

---

## 6 — druzhba mezhdu pitomcami

**cto eto dolzno delat**

dva profilia pitomcev mogut byt sviazany kak druzia, znakom y po parku, sosedi, uchastniki treninga ili pitomcy, zhivusc ie vmeste

**pocemu eto nuzno**

socialnaja istoria pitomca moz et byt ne menee vaznoj, chem socialnye sviazi ego xoziajina

**kak eto dolzno rabotat po logike**

zapros otpravliaetsia ot imeni odnogo pitomca, no podtverzhdaetsia upravliajusc imi obeix profilej

druzhba ne dolzna ozna chat, cto pitomcy garantir ovanno bezopasno sovmestimy

**dlia kogo i dlia kakoj celi**

dlia organizacii progulok, obsch ix albomov, sobytij, druzeskix grupp i dolgosrocnoj socialnoj istorii pitomca

**kakoj rezultat dolzen byt dostignut**

v profile vidny realnye druzia pitomca, no algoritm ne vydajot socialnuju metku za professionalnuju ocenk u sovmestimosti

---

## 7 — znakomstvo bez druzhby

**cto eto dolzno delat**

sistema moz et soxranit slab uju sviaz

* vstrechalis na sobytii
* byli na odn oj progulke
* uchastvovali v treninge
* znakom y po gruppe
* odin raz obschalis

**pocemu eto nuzno**

ne kazdyj kontakt dolzen srazu prevrashchatsia v druzhbu ili podpis ku

**kak eto dolzno rabotat po logike**

takaja sviaz moz et byt lichnoj zametkoj, vzaimno podtverzhdennoj ili avtomaticeski predlozhennoj po sobytiju

ona ne dolzna avtomaticeski otkryvat kontent dlia druzej

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat zapomnit kontakt i resit pozze, prodolzhat li obsch enie

**kakoj rezultat dolzen byt dostignut**

socialn yj graf ne zastavljaet polzovatelia vybirat tolko mezhdu polnym otsutstviem sviazi i blizkoj druzhboj

---

## 8 — semejnaja sviaz ne ravnjaetsia druzhbe

**cto eto dolzno delat**

uchastniki odnogo semejnogo prostranstva dolzny byt sviazany otdelnoj semejnoj rolju

**pocemu eto nuzno**

clen semji moz et upravliat dnevnikom pitomca, no ne xotet publicno pokaz yvat druzhbu ili lichnye publikacii

**kak eto dolzno rabotat po logike**

semejnaia rol opredeliaet prava na uxod, a socialnaja druzhba opredeliaet vidimost socialnogo kontenta

oni mogut sushestvovat odnovremenno ili otdelno

**dlia kogo i dlia kakoj celi**

dlia par, rodstvennikov, detej, sovladelcev i postojannyx smotritelej

**kakoj rezultat dolzen byt dostignut**

upravlenie pitomcem ne zavisit ot publicnogo socialnogo statusa mezhdu liudmi

---

## 9 — professionalnaja sviaz

**cto eto dolzno delat**

xoziajin i specialist mogut byt sviazany cerez

* konsultaciju
* napravlenie
* kurs
* trening
* gruming
* reabilitaciju
* perederzku
* druguju uslugu

**pocemu eto nuzno**

specialist ne obyazatelno dolzen stanovitsia lichnym drugom klienta, ctob polucat nuzn yj vrem ennyj dostup i otpravliat professionalnye obnovleniia

**kak eto dolzno rabotat po logike**

sviaz sozdaetsia v ramkax konkretnoj uslugi, imeet srok, prava, dostupnye dannye i istoriju

posle zaversheniia uslugi ona moz et ostatsia v istorii, no aktivnye prava dolzny byt otzyvany

**dlia kogo i dlia kakoj celi**

dlia veterinarov, kinologov, grumerov, sitterov, reabilitologov, fotografov i klientov

**kakoj rezultat dolzen byt dostignut**

professionalnoe vzaimodejstvie ne smeshivaetsia s lichnoj druzhboj i ne daet bessrocn yj dostup k dannym

---

## 10 — sviaz cerez gruppu

**cto eto dolzno delat**

dva polzovatelia mogut videt, cto oni sosto iat v odn oj gruppe

**pocemu eto nuzno**

obschaja gruppa sozdaet socialnyj kontekst i moz et pomoch ocenit relevantnost zaprosa

**kak eto dolzno rabotat po logike**

mozhno pokazat

* odnu obsc uju gruppu
* neskolko grupp
* tolko kolichestvo
* nichego, esli gruppa zakrytaja ili skrytaja

**dlia kogo i dlia kakoj celi**

dlia tematiceskix, porodnyx, mestnyx, professionalnyx i volonterskix soobscestv

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet kontekst zaprosa, no zakryt oe uchastie v chuvstvitelnoj gruppe ne raskryvaetsia postoronnemu

---

## 11 — sviaz cerez sobytie

**cto eto dolzno delat**

uchastniki odnogo sobytija mogut posle nego najti drug druga i po zhelaniju ustanovit socialnuju sviaz

**pocemu eto nuzno**

realnoe sovmestnoe uchastie daet bolee pon iatnyj kontekst, chem slucajn yj profil iz globalnogo poisk a

**kak eto dolzno rabotat po logike**

organizator i uchastniki nast r aivajut, vidny li drugie uchastniki do sobytija, vo vremia nego ili posle

nikto ne dolzen avtomaticeski dobavliatsia v druzia

**dlia kogo i dlia kakoj celi**

dlia gruppovyx progulok, treningov, vystavok, vebinarov, fotosessij i volonterskix akcij

**kakoj rezultat dolzen byt dostignut**

posle uspeshnoj vstrechi polzovateli prosto prodolzajut kontakt, no ix uchastie ne prevrashch a etsia v obyazateln uju druzhbu

---

## 12 — vrem ennyj kontakt

**cto eto dolzno delat**

sistema dolzna podderzhivat kontakt, kotoryj avtomaticeski istekaet

**pocemu eto nuzno**

dlia perederzki, odnoj progulki, transporta, poiskovoj operacii ili razovogo sobytija bessrocnaja socialnaja sviaz ne nuzhna

**kak eto dolzno rabotat po logike**

vrem ennyj kontakt moz et davat

* chat
* kartocku pitomca
* vrem ennuju lokaciju
* instrukcii
* kontakt organizatora
* srocnye uvedomlenija

posle sroka aktivnye prava otzyvajutsia

**dlia kogo i dlia kakoj celi**

dlia sitterov, voditelej, volonterov, organizatorov i uchastnikov razovogo vzaimodejstviia

**kakoj rezultat dolzen byt dostignut**

posle zaversheniia zadaci vrem ennyj uchastnik ne soxraniaet lishnie socialnye i lokacionnye prava

---

## 13 — blizkij krug

**cto eto dolzno delat**

xoziajin moz et sozdat ogranichenn yj spisok samyx doverennyx liudej i profilej

**pocemu eto nuzno**

nekotorye fotografii, istorii, srocnye signaly i semejnye obnovleniia nelzia publikovat vsem druzjam ili podpiscikam

**kak eto dolzno rabotat po logike**

blizkij krug moz et polucat

* zakrytye posty
* srocn oe uvedomlenie o propazhe
* priglasheniia na lichnye progulki
* vrem ennyj status
* semejnye fotografii
* bazovye ekstrennye instrukcii

**dlia kogo i dlia kakoj celi**

dlia blizkix druzej, rodstvennikov, sosed ej i doverennyx xoziaev

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et delitsia bolee lichnym kontentom bez publicacii vsej socialnoj auditorii

---

## 14 — blizkij krug ne ravnjaetsia sovladeniju

**cto eto dolzno delat**

dobavlenie v blizkij krug ne dolzno davat prava na redaktirovanie profilia, medkartocku, gps ili upravlenie ustrojstvami

**pocemu eto nuzno**

socialnoe doverie i administrativnoe pravo upravliat pitomcem javliajutsia raznymi veschami

**kak eto dolzno rabotat po logike**

dlia upravleniia pitomcem sozdaetsia otdelnoe priglashenie s rolju i konkretnymi pravami

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat pokaz yvat zakrytyj kontent, no ne peredavat kontrol

**kakoj rezultat dolzen byt dostignut**

blizkij drug moz et videt lichn uju istoriju, no ne moz et izmenit lekarstvo, xoziajina ili adres dostavki

---

## 15 — doverenn yj ekstrennyj kontakt

**cto eto dolzno delat**

polzovatel moz et naznacit kontaktnogo celoveka na slucaj

* propazhi pitomca
* hospitalizacii xoziajina
* nescastnogo slucaia
* srocn oj kliniki
* nedostupnosti osnovnogo xoziajina
* problemy s sitterom

**pocemu eto nuzno**

socialn yj drug ne obyazatelno znaet, cto emu nuzno delat v ekstrenn oj situacii

**kak eto dolzno rabotat po logike**

ekstrennyj kontakt polucaet zaranee opredelennye prava i ne polucaet poln yj dostup srazu

aktivacija moz et trebovat konkretnogo sobytija, vremeni ili podtverzhdeniia

**dlia kogo i dlia kakoj celi**

dlia xoziaev, kotorye xotiat obespecit nepreryvnost uxoda

**kakoj rezultat dolzen byt dostignut**

pri nedostupnosti osnovnogo xoziajina proverenn yj celovek polucaet instrukcii i moz et pomoch pitomcu

---

# zaprosy na druzhbu

## 16 — otpravka zaprosa

**cto eto dolzno delat**

polzovatel moz et otpravit zapros na druzhbu drugomu xoziajinu ili ot imeni pitomca drugomu profilem pitomca

**pocemu eto nuzno**

dvustoronnjaia sviaz dolzna sozdavatsia tolko posle soglasiia obeix storon

**kak eto dolzno rabotat po logike**

pered otpravkoj sistema proveriaet

* nastojki poluchatelia
* blokirovki
* vozrastnye ogranicheniia
* limit zaprosov
* nalichie aktivnogo ili starogo zaprosa
* podozriteln uju aktivnost
* dopustimyj tip sviazi

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat sozdat podtverzhdenn yj vzaimnyj kontakt

**kakoj rezultat dolzen byt dostignut**

odin zapros sozdaetsia odin raz i dostavliaetsia tolko togda, kogda poluchatel razreshaet takoi tip obrasheniia

---

## 17 — vybor profilia, ot imeni kotorogo otpravliaetsia zapros

**cto eto dolzno delat**

polzovatel dolzen vybrat

* svoj lichnyj profil
* konkretnogo pitomca
* professionalnyj profil
* organizaciju pri nalichii prava

**pocemu eto nuzno**

xoziajin moz et upravliat neskolkimi pitomcami i professionalnymi roliami

**kak eto dolzno rabotat po logike**

interfejs jasno pokaz yvaet, ot kogo pridot zapros i kakoi tip sviazi sozdaetsia

**dlia kogo i dlia kakoj celi**

dlia polzovatelej s neskolkimi profiljami i roliami

**kakoj rezultat dolzen byt dostignut**

zapros na druzhbu dlia baksa ne otpravliaetsia slucajno ot professionalnogo profilia veterinara

---

## 18 — korotkoe soprovoditelnoe soobsenie

**cto eto dolzno delat**

k zaprosu mozno dobavit korotkij kontekst

naprimer

* my vstrechalis v parke
* nashi pitomcy byli na odn om treninge
* my sosto im v odn oj gruppe
* xocu priglasit vas na progulku

**pocemu eto nuzno**

pustoj zapros ot neznakomogo akkaunta c asto vygl iadit kak spam

**kak eto dolzno rabotat po logike**

soobsenie imeet korotkij limit, prohodit proverku na ugrozy, ssylki, kontaktnye dannye, reklamu i massovoe kopirovanie

**dlia kogo i dlia kakoj celi**

dlia novyx znakomstv, gde poluchateliu nuzno ponimat kontekst

**kakoj rezultat dolzen byt dostignut**

poluchatel moz et resit, znaet li on avtora i pocemu zapros byl otpravlen

---

## 19 — zapros bez soobsenija

**cto eto dolzno delat**

polzovatel moz et otpravit prostoj zapros bez obyazatelnogo teksta

**pocemu eto nuzno**

dlia znakom yx liudej ili profilej iz odn oj gruppy dopolnitelnoe soobsenie moz et byt lishnim

**kak eto dolzno rabotat po logike**

platforma moz et pokazat kontekst avtomaticeski

* tri obsc ix druga
* obschaja gruppa
* odno sobytie
* druzia pitomcev
* mestnoe soobscestvo

**dlia kogo i dlia kakoj celi**

dlia prostogo obnovleniia uze sushestvujusc ej socialnoj sviazi

**kakoj rezultat dolzen byt dostignut**

zapros ostajotsia bystrym, no poluchatel vse ravno vidit bazovyj kontekst

---

## 20 — nastojki poluceniia zaprosov

**cto eto dolzno delat**

polzovatel dolzen nastroit, ot kogo prinimat zaprosy

* ot vseh
* ot druzej druzej
* ot uchastnikov obsc ix grupp
* ot uchastnikov sobytij
* ot mestnyx profilej
* tolko po lichnoj ssylke
* ni ot kogo

**pocemu eto nuzno**

odin polzovatel ishchet novyx znakom yx, drugoj xocet obschatsia tolko s uze izvestnymi liudmi

**kak eto dolzno rabotat po logike**

nastojka proveriaetsia do otpravki, ctob zapreshchenn yj zapros ne pojavlialsia daze v ozhidanii

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej s raznym urovnem socialnoj otkrytosti

**kakoj rezultat dolzen byt dostignut**

polzovatel ne polucaet nezhelatelnye zaprosy iz kategorij, kotorye on zaranee zakryl

---

## 21 — statusy zaprosa

**cto eto dolzno delat**

zapros dolzen imet status

* cernovik
* otpravlen
* dostavlen
* ozhidaet resheniia
* prin iat
* otklonen
* otmenen otpravitelem
* iste k
* skryt poluchatelem
* zablokirovan sistemoj
* udal en posle zhaloby

**pocemu eto nuzno**

odin flazok ozhidaet ne opis yvaet vse realnye situacii

**kak eto dolzno rabotat po logike**

kazdyj status opredeliaet dostupnye dejstviia i uvedomlenija

**dlia kogo i dlia kakoj celi**

dlia otpravitelia, poluchatelia, moderacii i sistemy uvedomlenij

**kakoj rezultat dolzen byt dostignut**

obe storony ponimajut tekuscee sostojanie bez dublirujusc ix zaprosov i protivorechivy x knopok

---

## 22 — prin iatie zaprosa

**cto eto dolzno delat**

poluchatel moz et podtverdit druzhbu

**pocemu eto nuzno**

dvustoronnjaia sviaz trebuet osoznannogo soglasiia

**kak eto dolzno rabotat po logike**

pered prin iatiem mozno pokazat kratko

* cto izmenitsia
* kakoi kontent stanet dostupnym
* budut li vidny druzia
* budut li dostupny soobsenija
* mozno li pozze ogranichit

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat sozdavat kontroliruemye socialnye sviazi

**kakoj rezultat dolzen byt dostignut**

poluchatel prinimaet ne abstraktn uju knopku, a pon iatn yj nabor socialnyx posledstvij

---

## 23 — prin iatie s ogranichenn oj vidimostiu

**cto eto dolzno delat**

polzovatel moz et prin iat druzhbu, no ne otkryvat vse razdely dlia druzej

**pocemu eto nuzno**

druz ia mogut imet razn yj urov en blizosti

**kak eto dolzno rabotat po logike**

posle prin iatiia polzovatel moz et vybrat

* oby cn yj drug
* ogranichenn yj drug
* blizkij krug
* vidit tolko publicnoe
* ne vidit istorii
* ne vidit spisok druzej

**dlia kogo i dlia kakoj celi**

dlia novyx znakomstv i polzovatelej, kotorye xotiat postepenno povysat doverie

**kakoj rezultat dolzen byt dostignut**

druzhba ne zastavliaet odnovremenno otkryvat ves lichn yj profil

---

## 24 — otklonenie zaprosa

**cto eto dolzno delat**

poluchatel moz et otklonit zapros bez obyazatelnogo objasneniia

**pocemu eto nuzno**

socialnaja sviaz dolzna byt dobrovolnoj

**kak eto dolzno rabotat po logike**

otpravitel polucaet nejtraln yj status bez raskrytiia lichnoj prichiny

poluchatel moz et dopolnitelno zablokirovat ili ogranichit povtornye zaprosy

**dlia kogo i dlia kakoj celi**

dlia zascity lichnyx granic

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et skazat net bez konflikta i obyazatelnogo opravdaniia

---

## 25 — skrytoe otklonenie

**cto eto dolzno delat**

v nekotoryx situacijax poluchatel moz et ubrat zapros bez zametnogo signala otpravitelju

**pocemu eto nuzno**

pri presledovanii ili nekomfortnom kontakte priam oe otklonenie moz et vyzvat novuju eskalaciju

**kak eto dolzno rabotat po logike**

otpravitel vidit tolko, cto aktivnogo zaprosa bolshe net ili cto zapros iste k

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat zaversit kontakt bez dopolnitelnogo vnimaniia

**kakoj rezultat dolzen byt dostignut**

poluchatel zasciscaet svoi granicy, ne predostavliaja dopolnitelnyj signal problemnomu akkauntu

---

## 26 — otmena zaprosa otpravitelem

**cto eto dolzno delat**

otpravitel moz et otmenit esio ne prin iatyj zapros

**pocemu eto nuzno**

zapros mog byt otpravlen po oshibke, ne tomu profiliu ili iz slucajno vybrann oj roli

**kak eto dolzno rabotat po logike**

posle otmeny zapros ischezaet iz aktivnyx, a povtornaja otpravka moz et byt vrem enno ogranichena dlia zascity ot spama

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat ispravit oshibku

**kakoj rezultat dolzen byt dostignut**

slucajn yj zapros ne ostajotsia bessrocno v ozhidanii

---

## 27 — srok dejstviia zaprosa

**cto eto dolzno delat**

zapros moz et avtomaticeski istekat posle ustanovlennogo perioda

**pocemu eto nuzno**

spisok ozhidajusc ix zaprosov ne dolzen bessrocno soderzhat st ar y e i neaktualnye obrasheniia

**kak eto dolzno rabotat po logike**

pered istecheniem mozno ne otpravljat lishnie napominanija, esli poluchatel ne proiavliaet interesa

posle istecheniia otpravitel ne moz et srazu povtoriat zapros mnogo raz

**dlia kogo i dlia kakoj celi**

dlia podderzki cistogo i aktualnogo spiska zaprosov

**kakoj rezultat dolzen byt dostignut**

star y e zaprosy ne sozdajut davleniia i ne nakaplivajutsia godami

---

## 28 — povtorn yj zapros

**cto eto dolzno delat**

sistema moz et razresit povtorn yj zapros tolko posle razumnogo perioda ili izmeneniia konteksta

**pocemu eto nuzno**

neogranichennye povtory prevrashchajutsia v presledovanie

**kak eto dolzno rabotat po logike**

posle otkloneniia moz et dejstvovat

* vrem enn yj zapret
* poln yj zapret povtora
* razreshenie tolko po novomu sobytiju
* razreshenie tolko po priglasheniju poluchatelia

**dlia kogo i dlia kakoj celi**

dlia zascity poluchatelej ot navi azchivy x obrashenij

**kakoj rezultat dolzen byt dostignut**

odin polzovatel ne moz et ezhednevno otpravljat novyj zapros posle otkloneniia

---

## 29 — dublirujusc iesia zaprosy

**cto eto dolzno delat**

sistema dolzna predotvrashchat neskolko aktivnyx zaprosov mezhdu odnimi i temi ze profiljami

**pocemu eto nuzno**

medlennyj internet, povtorn oe nazhatie ili dva upravliajusc ix odnogo pitomca mogut sozdat dublikaty

**kak eto dolzno rabotat po logike**

odin tip sviazi mezhdu konkret n ymi profiljami moz et imet tolko odin aktivnyj zapros

**dlia kogo i dlia kakoj celi**

dlia stabilnoj raboty pri povtornyx setevyx dejstviiax

**kakoj rezultat dolzen byt dostignut**

poluchatel ne polucaet neskolko odinakov yx uvedomlenij

---

## 30 — zapros ot neskolkix upravliajusc ix odnogo pitomca

**cto eto dolzno delat**

esli odin sovladelec uze otpravil zapros ot imeni pitomca, drugoj dolzen videt ego status

**pocemu eto nuzno**

u odnogo profilia moz et byt neskolko upravliajusc ix, kotorye ne dolzny sozdaivat protivorechivye dejstviia

**kak eto dolzno rabotat po logike**

vse upolnomochennye upravliajusc ie vidat aktivn yj zapros i realnogo avtora

pravo otmenit ili prin iat obratn yj zapros zavisit ot roli

**dlia kogo i dlia kakoj celi**

dlia sem ej i priutov s neskolkimi administratorami

**kakoj rezultat dolzen byt dostignut**

odin profil pitomca ne otpravliaet neskolko odinakov yx zaprosov ot raznyx clenov komandy

---

# zascita ot spama v zaprosax

## 31 — limit castoty

**cto eto dolzno delat**

platforma dolzna ogranichivat kolichestvo novyx zaprosov za korotkij period

**pocemu eto nuzno**

moshennik ili bot moz et massovo otpravljat zaprosy tysiacam polzovatelej

**kak eto dolzno rabotat po logike**

limit dolzen ucit yvat

* vozrast akkaunta
* podtverzhdenie kontakta
* procent prin iatiia
* kolichestvo zhalob
* odinakov yj tekst
* skorost dejstvij
* tip profilia
* nalichie obsc ix sviazej

**dlia kogo i dlia kakoj celi**

dlia zascity vsego soobscestva ot massovogo spama

**kakoj rezultat dolzen byt dostignut**

normalnyj polzovatel moz et dobavliat realnyx znakom yx, a avtomaticeskaia rassylka bystro ostanavlivaetsia

---

## 32 — analiza odinakov yx soobsenij

**cto eto dolzno delat**

sistema dolzna zam ec at, kogda odin akkaunt otpravliaet odinakov yj tekst mnogim polzovateliam

**pocemu eto nuzno**

massovaja reklama i moshenniceskie sxemy c asto ispolzujut odin shablon

**kak eto dolzno rabotat po logike**

odinakov yj tekst ne obyazatelno oznachaet narushenie, no povysaet risk i moz et potrebovat proverku ili vrem ennyj limit

**dlia kogo i dlia kakoj celi**

dlia zascity ot botov, prodavcov spama i moshennikov

**kakoj rezultat dolzen byt dostignut**

massovoe odinakovoe obrashenie ne rasprostraniaetsia bezogranichno

---

## 33 — novyj akkaunt s mnogo zaprosov

**cto eto dolzno delat**

novyj akkaunt, kotoryj srazu otpravliaet mnogo zaprosov, dolzen poluchit ogranichenie ili dopolnitelnuju proverku

**pocemu eto nuzno**

realn yj novyj polzovatel oby cno ne znaet sotni profilej v pervye minuty

**kak eto dolzno rabotat po logike**

sistema moz et predlozhit

* podtverdit email
* zapolnit bazovyj profil
* projti zascitu ot avtomatizacii
* podozdat
* otpravljat zaprosy tolko profilem s obsc im kontekstom

**dlia kogo i dlia kakoj celi**

dlia zascity ot massovo sozda v aemyx akkauntov

**kakoj rezultat dolzen byt dostignut**

novyj bot ne moz et srazu rassylat moshenniceskie zaprosy vsemu gorodu

---

## 34 — nizkij procent prin iatiia

**cto eto dolzno delat**

sistema moz et zam ec at, cto bolshinstvo zaprosov akkaunta otkloniaetsia ili ignoriruetsia

**pocemu eto nuzno**

eto moz et ukazyvat na slucajn uju massovuju rassylku ili nezhelatelnoe povedenie

**kak eto dolzno rabotat po logike**

snachala mozno pokazat obrazovateln yj signal, pozze ogranichit novye zaprosy, a pri zhalobax peredat akkaunt na proverku

**dlia kogo i dlia kakoj celi**

dlia zascity polzovatelej bez nemedlennogo nakazaniia za odin neudacn yj zapros

**kakoj rezultat dolzen byt dostignut**

navi azchiv yj akkaunt ne moz et bessrocno ignorirovat socialnye granicy drugih

---

## 35 — zaprosy ot zablokirovannyx akkauntov

**cto eto dolzno delat**

zablokirovann yj polzovatel ne dolzen moc otpravljat novye zaprosy cerez drugie upravliaemye profilej

**pocemu eto nuzno**

bez etogo blokirovku legko obojti cerez profil drugogo pitomca, gruppu ili organizaciju

**kak eto dolzno rabotat po logike**

pri blokirovke mozno vybrat

* zablokirovat tolko etot profil
* zablokirovat akkaunt i vse upravliaemye profilej
* zablokirovat novye profilej etogo akkaunta
* zablokirovat lichnye i professionalnye obrasheniia

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye zascischajutsia ot presledovaniia

**kakoj rezultat dolzen byt dostignut**

blokirovka dejstvitelno prekrashchaet novye socialnye kontakty v ramkax platformy

---

# podpiski

## 36 — podpiska na publicnogo pitomca

**cto eto dolzno delat**

polzovatel moz et podpisatsia na publicnye posty konkretnogo pitomca

**pocemu eto nuzno**

polzovatel moz et interesovatsia kontentom pitomca, ne znaja lichno ego xoziajina

**kak eto dolzno rabotat po logike**

podpiska otobrazaetsia kak sviaz s profilem pitomca, a ne avtomaticeski so vsemi profilej ego xoziajina

**dlia kogo i dlia kakoj celi**

dlia populiarnyx profilej, priutskix zivotnyx, blogov, fotografij i obrazovatelnyx istorij

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet obnovleniia odnogo pitomca bez avtomaticeskoj podpiski na lichn yj profil xoziajina

---

## 37 — podpiska na xoziajina

**cto eto dolzno delat**

polzovatel moz et sledit za publicnym kontentom konkretnogo xoziajina

**pocemu eto nuzno**

xoziajin moz et publikovat materialy o neskolkix pitomcax, volonterstve, sobytijax ili opyte

**kak eto dolzno rabotat po logike**

podpiska na xoziajina ne dolzna avtomaticeski podpis yvat na vse profilej ego pitomcev, esli on ne razresil obsch uju lentu

**dlia kogo i dlia kakoj celi**

dlia avt orov, volonterov, aktivnyx xoziaev i blogerov

**kakoj rezultat dolzen byt dostignut**

polzovatel sam vybiraet, interesuet li ego celovek, konkretn yj pitomec ili oba

---

## 38 — podpiska na specialista

**cto eto dolzno delat**

polzovatel moz et podpisatsia na professionalnye publikacii veterinara, kinologa, grumera ili drugogo specialista

**pocemu eto nuzno**

polzovatel moz et xotet c itat materialy, ne stanovias klientom ili lichnym drugom

**kak eto dolzno rabotat po logike**

professionalnaia podpiska otdelena ot lichnogo profilia specialista

reklama, sponsorskii kontent i lichn y e posty dolzny imet pon iatnye metki

**dlia kogo i dlia kakoj celi**

dlia xoziaev, kotorye xotiat polucat obrazovateln yj kontent

**kakoj rezultat dolzen byt dostignut**

professionalnaja auditorija ne polucaet avtomaticeski lichnye posty specialista

---

## 39 — podpiska na priut ili organizaciju

**cto eto dolzno delat**

polzovatel moz et sledit za

* novymi pitomcami
* potrebnostiami
* sobytijami
* volonterstvom
* otchetami
* istoriiami adopcii
* srocn ymi obrasheni iami

**pocemu eto nuzno**

priut ne javliaetsia lichnym drugom, no ego obnovleniia mogut byt vazny polzovateliu

**kak eto dolzno rabotat po logike**

polzovatel vybiraet kategorii uvedomlenij, ctob ne polucat vse tipy publikacij

**dlia kogo i dlia kakoj celi**

dlia donorov, volonterov, buduscix xoziaev i mestnogo soobscestva

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et sledit tolko za adopciej ili srocn ymi potrebnostiami bez polnogo potoka kontenta

---

## 40 — zapros na podpis ku zakrytogo profilia

**cto eto dolzno delat**

zakryt yj profil moz et trebovat odobreniia kazdogo novogo podpiscika

**pocemu eto nuzno**

xoziajin moz et xotet delitsia kontentom s ogranichenn ym krugom, ne perevodia vse kontakty v druzhbu

**kak eto dolzno rabotat po logike**

zapros na podpis ku otdelen ot zaprosa na druzhbu

poluchatel moz et

* prin iat
* otklonit
* ogranichit
* ubrat pozze
* zablokirovat

**dlia kogo i dlia kakoj celi**

dlia zakrytyx profilej pitomcev i xoziaev

**kakoj rezultat dolzen byt dostignut**

xoziajin kontroliruet auditoriju, ne obyazatelno sozdavaja dvustoronn uju druzhbu

---

## 41 — udaleniie podpiscika

**cto eto dolzno delat**

vladelec profilia moz et udal it konkretnogo podpiscika bez polnoj blokirovki

**pocemu eto nuzno**

inogda celovek bolshe ne dolzen videt zakrytyj kontent, no polnaja blokirovka ne nuzhna

**kak eto dolzno rabotat po logike**

udalenn yj podpiscik ter iaet dostup k buduscemu kontentu i po neobxodimosti k staromu zakrytomu kontentu

**dlia kogo i dlia kakoj celi**

dlia kontrolia auditorii zakrytogo profilia

**kakoj rezultat dolzen byt dostignut**

xoziajin moz et izmenit sostav auditorii bez konflikta i bez publicnogo objasneniia

---

## 42 — otpiska

**cto eto dolzno delat**

podpiscik moz et v liuboe vremia otpisatsia

**pocemu eto nuzno**

interes moz et izmenitsia, a podpiska dolzna byt dobrovolnoj

**kak eto dolzno rabotat po logike**

otpiska ne treb uet podtverzhdeniia avtora i ne otpravliaet emu dramaticeskoe uvedomlenie po umolcaniju

**dlia kogo i dlia kakoj celi**

dlia vseh podpiscikov

**kakoj rezultat dolzen byt dostignut**

polzovatel sam kontroliruet lentu i moz et tixo prekratit sledit za profilem

---

## 43 — priostanovka podpiski bez otpiski

**cto eto dolzno delat**

polzovatel moz et vrem enno ne videt publikacii profilia, ne udal iaja podpis ku

**pocemu eto nuzno**

inogda kontenta slishkom mnogo ili tema vrem enno neaktualna

**kak eto dolzno rabotat po logike**

mozno vybrat

* na sutki
* na nedeliu
* na mesiac
* do rucnogo vkliucheniia
* skryt tolko istorii
* skryt tolko video
* skryt tolko reklamnye posty

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat upravliat nagruzk oj bez razryva socialnoj sviazi

**kakoj rezultat dolzen byt dostignut**

lenta stanovitsia spok o jnee, a podpiska soxraniaetsia

---

## 44 — vidimost spiska podpiscikov

**cto eto dolzno delat**

vladelec profilia dolzen vybrat

* pokaz yvat vseh
* pokaz yvat tolko obsc ix
* pokaz yvat tolko kolichestvo
* ne pokaz yvat spisok
* pokaz yvat tolko upravliajuscim

**pocemu eto nuzno**

spisok podpiscikov moz et raskryvat socialnye, professionalnye i mestnye sviazi

**kak eto dolzno rabotat po logike**

zakrytyj spisok ne dolzen raskryvatsia cerez poisk, api, podskazki ili rekomendacii

**dlia kogo i dlia kakoj celi**

dlia publicnyx i zakrytyx profilej

**kakoj rezultat dolzen byt dostignut**

populiarnost profilia moz et byt vidna cifroj bez publicacii polnogo spiska liudej

---

## 45 — zascita ot nakrutki podpiscikov

**cto eto dolzno delat**

sistema dolzna vyiavliat

* massovye falshiv y e akkaunty
* kuplennyx podpiscikov
* odinakovoe povedenie
* avtomaticeskie podpiski
* krugovuju nakrutku
* mnogo akkauntov s odnogo istochnika
* podpiski srazu posle registracii bez drugoj aktivnosti

**pocemu eto nuzno**

falshiv oe kolichestvo podpiscikov iskazhaet doverie, konkursy, reklamu i rekomendacii

**kak eto dolzno rabotat po logike**

podozritelnye podpiski mogut ne ucit yvatsia v rejtinge do proverki

realnyj vladelec polucaet vozmoznost apellacii

**dlia kogo i dlia kakoj celi**

dlia vsego soobscestva i chestn oj sistemy popularnosti

**kakoj rezultat dolzen byt dostignut**

bolshaja cifra bolee tocn o otrazaet realn uju auditoriju, a ne kuplennyx botov

---

# druzhba pitomcev i sovmestimost

## 46 — zapros druzhby mezhdu pitomcami

**cto eto dolzno delat**

upravliajusc ij odnogo pitomca moz et predlozhit sviaz s drugim profilem

**pocemu eto nuzno**

pitomcy mogut byt realnymi druzjami po progulkam, treningu, sovmestnomu domu ili sobytijam

**kak eto dolzno rabotat po logike**

zapros dolzen ukazyvat

* kakie pitomcy
* kto realn yj otpravitel
* otkuda oni znakom y
* kakoi tip druzhby predlagaetsia
* est li plan pervoj vstrechi

**dlia kogo i dlia kakoj celi**

dlia xoziaev, kotorye xotiat soxranit ili nacat socialn uju sviaz pitomcev

**kakoj rezultat dolzen byt dostignut**

poluchatel ponimaet, o kakom pitomce i kontekste idet rec

---

## 47 — podtverzhdenie obeimi storonami

**cto eto dolzno delat**

druzhba pitomcev sozdaetsia tolko posle soglasiia upolnomochennyx upravliajusc ix obeix profilej

**pocemu eto nuzno**

odin xoziajin ne dolzen samostojatelno objavljat cuzego pitomca druzeskim ili socialno sviazannym

**kak eto dolzno rabotat po logike**

pri neskolkix sovladelcax profil dolzen imet pravilo, kto moz et podtverzhdat socialnye sviazi

**dlia kogo i dlia kakoj celi**

dlia profilej s odnim xoziajinom, sem ej, priutov i perederzhek

**kakoj rezultat dolzen byt dostignut**

socialnaja sviaz ne pojavliaetsia v cuzo m profile bez soglasiia

---

## 48 — tip druzhby pitomcev

**cto eto dolzno delat**

mozno vybrat

* zhivut vmeste
* blizkie druzia
* znakom y po parku
* parallel n y e progulki
* treningovaia para
* uchastniki odn oj gruppy
* onlajn druzia
* rodstvenniki
* druzia po priutu
* kontakt tolko s distanciej

**pocemu eto nuzno**

druz ia ne obyazatelno igrajut bez povodka ili tesno kontaktirujut

**kak eto dolzno rabotat po logike**

tip opisyvaet socialn yj kontekst, no ne garantir uet bezopasnost sledujuscej vstrechi

**dlia kogo i dlia kakoj celi**

dlia raznyx form socialnogo vzaimodejstviia

**kakoj rezultat dolzen byt dostignut**

profilej ne polucajut nepon iatnuju universalnuju metku druzia bez konteksta

---

## 49 — bazovaja kartocka sovmestimosti

**cto eto dolzno delat**

pered predlozheniem vstrechi xoziaevam mozno pokazat razreshennye faktory

* vid
* razmer
* vozrastn uju gruppu
* uroven aktivnosti
* stil igry
* potre bnost v distancii
* opyt s drugimi zivotnymi
* predpoctitelnoe mesto
* ogranicheniia po zdorovju v obobschonnom vide

**pocemu eto nuzno**

odnogo sovpadeniia po porode ili gorodu nedostatochno dlia poleznoj rekomendacii

**kak eto dolzno rabotat po logike**

kartocka pokaz yvaet sovpadeniia, razlicija i otsutstvujusc ie dannye, no ne vydajot absoliutn yj procent sovmestimosti

**dlia kogo i dlia kakoj celi**

dlia xoziaev, planirujusc ix perv uju vstrechu

**kakoj rezultat dolzen byt dostignut**

xoziajie zaranee vidat vaznye razlicija i mogut vybrat podxodiasc ij format kontakta

---

## 50 — sovmestimost ne dolzna byt garantiej

**cto eto dolzno delat**

sistema dolzna jasno objasniat, cto algoritm ne moz et garantir ovat povedenie zivotnyx

**pocemu eto nuzno**

povedenie zavisit ot mesta, stressa, zdorovja, povodka, xoziaev, drugih zivotnyx i mnogix drugih faktorov

**kak eto dolzno rabotat po logike**

nelzia ispolzovat formulirovki

* idealno sovmestimy
* bezopasno na sto procentov
* tocno podruzhatsia
* mozno srazu otpustit bez povodka

**dlia kogo i dlia kakoj celi**

dlia zascity xoziaev i pitomcev ot opasnoj uverennosti

**kakoj rezultat dolzen byt dostignut**

algoritm pomogaet podgotovitsia, no ne zameniaet nabludenie, kontrol i professionalnuju pomosc

---

## 51 — stil igry

**cto eto dolzno delat**

profil moz et ukazat

* spokojnaia igra
* pogonia
* borba telom
* igra s miacom
* poisk
* parallel n oe prisutstvie
* ne interesuetsia igroj
* nuzhen korotkij kontakt
* stil neizvesten

**pocemu eto nuzno**

dva druzheliubnyx pitomca mogut imet ocen raznye stili igry

**kak eto dolzno rabotat po logike**

stil ukazyvaetsia kak nabludenie xoziajina i moz et meniatcia po situacii

**dlia kogo i dlia kakoj celi**

dlia podbora bolee komfortn yx socialnyx kontaktov

**kakoj rezultat dolzen byt dostignut**

spokojnomu pitomcu ne rekomendujutsia tolko ocen gruby e i intensivnye igry bez preduprezhdeniia

---

## 52 — potre bnost v distancii

**cto eto dolzno delat**

profil moz et ukazat

* moz et podxodit blizko
* luchshe nacat s bolshoj distancii
* podxodit tolko parallel n a ia progulka
* ne nuzhen priam oj kontakt
* distancija zavisit ot situacii
* neizvestno

**pocemu eto nuzno**

nekotorye pitomcy mogut spokojno gul iat riadom, no ne xotiat tesnogo kontakta

**kak eto dolzno rabotat po logike**

eta informacija pokazyvaetsia pri plan iro vanii vstrechi i v zadace organizatora

**dlia kogo i dlia kakoj celi**

dlia trevoznyx, ostoroznyx, pozhilyx ili reaktivnyx pitomcev

**kakoj rezultat dolzen byt dostignut**

vstrecha ne nac inaetsia s rezkogo tesnogo znakomstva, esli odnomu pitomcu nuzna distancija

---

## 53 — razmer i fiziceskaia bezopasnost

**cto eto dolzno delat**

sistema moz et pokazat znachiteln uju raznicu v razmere i stile igry

**pocemu eto nuzno**

daze bez agressii krupn yj aktivn yj pitomec moz et slucajno travmirovat malenjkogo

**kak eto dolzno rabotat po logike**

preduprezhdenie ne dolzno zapreshchat kontakt avtomaticeski, no dolzno predlagat

* kontrol
* korotkuju vstrechu
* podxodiasc ee mesto
* odsutstvie tesn oj tolpy
* razdelenie pri neobxodimosti

**dlia kogo i dlia kakoj celi**

dlia profilej s bolshoj raznicej v razmere, vozraste ili podviznosti

**kakoj rezultat dolzen byt dostignut**

xoziajie zaranee plan irujut fiziceski bezopasn yj format

---

## 54 — vozrast i uroven energii

**cto eto dolzno delat**

sistema moz et sravnivat vozrastn uju gruppu i uroven aktivnosti

**pocemu eto nuzno**

shenok s vysokoj energiej i pozhiloj pitomec mogut imet raznye potrebnosti

**kak eto dolzno rabotat po logike**

algoritm pokaz yvaet razlicie i predlagaet podxodiasc ij format

naprimer korotkaja spokojnaia progulka vmesto dlinn oj aktivnoj igry

**dlia kogo i dlia kakoj celi**

dlia xoziaev pitomcev raznogo vozrasta

**kakoj rezultat dolzen byt dostignut**

rekomendacija ne podtalkivaet pozhilogo ili vosstanavlivajusc egosia pitomca k peregruzke

---

## 55 — mezhvidovaja druzhba

**cto eto dolzno delat**

profil moz et imet sviaz s pitomcem drugogo vida

naprimer koska i sobaka, popugaj i celovek, loshad i sobaka

**pocemu eto nuzno**

realnye socialnye sviazi ne ogranicheny odnim vidom

**kak eto dolzno rabotat po logike**

takaja sviaz ne dolzna avtomaticeski predlagat fiziceskuju vstrechu ili blizkij kontakt

nuzno ucit yvat bezopasnost konkretnyx vidov

**dlia kogo i dlia kakoj celi**

dlia domashnix grupp, obrazovatelnyx profilej i realnyx mezhvidovyx sviazej

**kakoj rezultat dolzen byt dostignut**

socialnaia istoria otrazaet realnost, no ne prodvigaet potencialno opasnyj kontakt

---

## 56 — onlajn druzhba bez realnoj vstrechi

**cto eto dolzno delat**

pitomcy ili xoziajie mogut byt onlajn druzjami, daze esli zhivut v raznyx stranax

**pocemu eto nuzno**

socialnaja podderzhka, obmen fotografijami i tematiceskoe obsch enie ne trebu jut fiziceskoj vstrechi

**kak eto dolzno rabotat po logike**

profil moz et ukazat tip onlajn druzia, chtob sistema ne predlagala lokalnye progulki

**dlia kogo i dlia kakoj celi**

dlia mezhdunarodnogo soobscestva, redkix vidov i tematiceskix grupp

**kakoj rezultat dolzen byt dostignut**

polzovateli mogut obschatsia po interesam bez loznoj lokalnoj rekomendacii

---

# pervaja vstrecha

## 57 — predlozhenie pervoj vstrechi

**cto eto dolzno delat**

posle socialnogo kontakta xoziajin moz et predlozhit bezopasn uju perv uju vstrechu

**pocemu eto nuzno**

perehod iz onlajn obseniia v realn oe trebuet dopolnitelnoj zascity liudej i zivotnyx

**kak eto dolzno rabotat po logike**

predlozhenie soderzit

* pitomcev
* format
* publicnoe mesto
* datu
* vremia
* prodolzhitelnost
* osobye instrukcii
* kontaktn yj kanal
* plan otmeny

**dlia kogo i dlia kakoj celi**

dlia xoziaev, kotorye resili organizovat realn oe znakomstvo

**kakoj rezultat dolzen byt dostignut**

vstrecha imeet pon iatn yj plan, a ne proisxodit po slucajnomu obmenu adresami v chat e

---

## 58 — publicnoe mesto dlia pervoj vstrechi

**cto eto dolzno delat**

platforma dolzna rekomendovat bezopasnye publicnye mesta

* park
* ploscadku s podxodiasc imi pravilami
* territoriju trening centra
* organizovann oe sobytie
* drugoe proverenn oe mesto

**pocemu eto nuzno**

peredaca domashnego adresa neznakomomu celoveku povysaet risk

**kak eto dolzno rabotat po logike**

sistema moz et pokazat

* pravila mesta
* razmer
* og razhdenie
* zagruzhennost po dostupnym dannym
* osveschenie
* dostupnost
* parkovku
* zony dlia spokojnoj vstrechi

**dlia kogo i dlia kakoj celi**

dlia novyx socialnyx kontaktov

**kakoj rezultat dolzen byt dostignut**

pervaja vstrecha ne trebuet raskrytiia doma i prohodit v bolee kontroliruemoj srede

---

## 59 — parallel n a ia progulka

**cto eto dolzno delat**

platforma moz et predlozhit format, pri kotorom pitomcy snachala idut na distancii v odnom napravlenii

**pocemu eto nuzno**

priam oe tesnoe znakomstvo licom k licu moz et byt slishkom rezkim dlia nekotoryx pitomcev

**kak eto dolzno rabotat po logike**

plan moz et ukazyvat

* nachaln uju distanciju
* marshrut
* prodolzhitelnost
* otsutstvie prinuditel nogo kontakta
* vozmoznost uvelichit distanciju
* kriterii zaversheniia

**dlia kogo i dlia kakoj celi**

dlia ostoroznyx, trevoznyx i neznakom yx pitomcev

**kakoj rezultat dolzen byt dostignut**

xoziajie mogut ocenit povedenie bez rezkogo fiziceskogo sb l izheniia

---

## 60 — ogranichenie prodolzhitelnosti pervoj vstrechi

**cto eto dolzno delat**

sistema moz et rekomendovat korotk uju perv uju vstrechu s vozmoznostiu zaversit ranshe

**pocemu eto nuzno**

ustalost i nakoplenie stressa mogut uxudshit povedenie daze pri xoro shem nacale

**kak eto dolzno rabotat po logike**

organizatory ukazyvajut primernoe vremia, no mogut zaversit vstrechu bez negativn oj ocenki

**dlia kogo i dlia kakoj celi**

dlia vseh novyx znakomstv pitomcev

**kakoj rezultat dolzen byt dostignut**

vstrecha zavershaetsia na spokojnom etape, a ne prodolzaetsia tolko radi vypolneniia plana

---

## 61 — instrukcii obeix xoziaev

**cto eto dolzno delat**

pered vstrechej kazdaia storona moz et podelitsia

* cego izbegat
* kak podxodit
* kakoi povodok ispolzuetsia
* est li triggeri
* nuzhna li distancija
* mozno li davat lakomstva
* est li ogranichenie aktivnosti

**pocemu eto nuzno**

xoziajin luchshe znaet bazovye granicy svoego pitomca

**kak eto dolzno rabotat po logike**

pokazyvajutsia tolko prakticeski neobxodimye dannye, bez peredaci polnoj medkartocki

**dlia kogo i dlia kakoj celi**

dlia bezopasnoj podgotovki obeix storon

**kakoj rezultat dolzen byt dostignut**

kazdyj xoziajin znaet, cto mozno i cego ne nado delat s cuzim pitomcem

---

## 62 — status vakcinacii bez polnoj medkartocki

**cto eto dolzno delat**

pri nekotoryx vstrechax xoziajin moz et dobrovolno pokazat obobschonn yj status vakcinacii

**pocemu eto nuzno**

dlia gruppovyx aktivnosti moz et byt vazen aktualn yj profilakticeskij status, no poln yj pasport ne nuzhen

**kak eto dolzno rabotat po logike**

mozno pokazat

* aktualno po podtverzhdennym dannym
* ukazano xoziajinom
* trebuet obnovleniia
* ne pokaz yvaetsia

**dlia kogo i dlia kakoj celi**

dlia organizatorov i uchastnikov, kotorym nuzhen minimaln yj bezopasnostn yj status

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et prin iat reshenie bez dostup a k diagnozam, lekarst vam i drugim dokumentam

---

## 63 — vrem ennoe raskrytie kontakta

**cto eto dolzno delat**

pered vstrechej storony mogut vrem enno obmeniatcia kontaktom cerez platformu

**pocemu eto nuzno**

nuzno soobscit ob opozdanii, izmenenii mesta ili otmene, no ne obyazatelno raskryvat lichn yj telefon

**kak eto dolzno rabotat po logike**

mozno ispolzovat

* chat
* vrem ennyj zvonok
* maskirovann yj nomer
* soobsenie organizatoru
* vrem ennuju ssylku

**dlia kogo i dlia kakoj celi**

dlia uchastnikov realn oj vstrechi

**kakoj rezultat dolzen byt dostignut**

storony mogut operativno sviazatsia, ne soxraniaja bessrocn yj dostup k lichnomu nomeru

---

## 64 — vrem ennaia lokacija

**cto eto dolzno delat**

polzovatel moz et dobrovolno podelitsia tekuscej lokaciej na korotkij period

**pocemu eto nuzno**

eto pomogaet najti drug druga v bolshom parke ili ponimat, cto celovek uze v puti

**kak eto dolzno rabotat po logike**

lokacija

* dostupna tolko konkretnym uchastnikam
* imeet srok
* moz et byt otzyvana
* avtomaticeski zakryvaetsia posle vstrechi
* ne soxr aniaetsia kak publicnyj marshrut

**dlia kogo i dlia kakoj celi**

dlia realnyx vstrech i gruppovyx progulok

**kakoj rezultat dolzen byt dostignut**

uchastniki naxodiat drug druga bez publicacii domashnej ili postojannoj gps istorii

---

## 65 — knopka ja na meste

**cto eto dolzno delat**

uchastnik moz et podtverdit pribytie bez peredaci tocn oj gps tocki

**pocemu eto nuzno**

ne vsem komfortno delitsia lokaciej

**kak eto dolzno rabotat po logike**

statusy

* v puti
* na meste
* opozdaju
* ne mogu najti
* nuzhna otmena
* uze ushel

**dlia kogo i dlia kakoj celi**

dlia koordinacii bez obyazatelnoj geolokacii

**kakoj rezultat dolzen byt dostignut**

vstrecha koordiniruetsia daze pri otkliuchenn oj lokacii

---

## 66 — otmena vstrechi

**cto eto dolzno delat**

liubaia storona moz et otmenit ili perenesti vstrechu

**pocemu eto nuzno**

pogoda, samocuvstvie, povedenie ili lichnye obstojatelstva mogut izmenitsia

**kak eto dolzno rabotat po logike**

otmena ne dolzna trebu vat detalnogo opravdaniia

mozno vybrat obsch uju prichinu i predlozhit novoe vremia

**dlia kogo i dlia kakoj celi**

dlia uvazheniia granic i bezopasnosti

**kakoj rezultat dolzen byt dostignut**

polzovatel ne cuvstvuet davleniia prodolzhat vstrechu, esli ona stala nekomfortnoj ili nebezopasnoj

---

## 67 — no show

**cto eto dolzno delat**

sistema moz et otmetit, cto uchastnik ne prishel i ne sviazalsia

**pocemu eto nuzno**

povtoriajusc eesia ne­i avki meshajut drugim polzovateliam i organizatoram

**kak eto dolzno rabotat po logike**

odin slucaj ne dolzen avtomaticeski sozda vat publicn uju negativnuju metku

pri povtorenii mozno ogranichit novye priglasheniia ili zaprosit podtverzhdenie

**dlia kogo i dlia kakoj celi**

dlia organizatorov i uchastnikov

**kakoj rezultat dolzen byt dostignut**

sistema snizaet kolichestvo neserioznyx dogovorennostej bez neobosnovannogo nakazaniia za odin slucaj

---

## 68 — otchet posle pervoj vstrechi

**cto eto dolzno delat**

kazdaia storona moz et lichno otmetit

* vstrecha prosla spokojno
* nuzhna bolshaia distancija
* pitomec ustal
* byla igra
* byl stress
* xotim vstrechatsia snova
* ne xotim prodolzhat
* nuzhna konsultacija

**pocemu eto nuzno**

rezultat pomogaet plan iro vat sledujusc ij kontakt i ulucshat lichnye rekomendacii

**kak eto dolzno rabotat po logike**

lichnye zametki ne publikujutsia avtomaticeski i ne stanov iatsia publicnym rejtingom pitomca

**dlia kogo i dlia kakoj celi**

dlia xoziaev, kotorye xotiat otslezhivat socialn yj opyt pitomca

**kakoj rezultat dolzen byt dostignut**

algoritm ne predlagaet povtorno tesn uju vstrechu, esli xoziajin otmetil, cto pitomcu nuzna distancija

---

## 69 — incident vo vremia vstrechi

**cto eto dolzno delat**

uchastnik moz et sozdat strukturirovann uju zapis incidenta

**pocemu eto nuzno**

pri konflikte vazny fakty, a ne tolko emocionalnye obvineniia v kommentariiax

**kak eto dolzno rabotat po logike**

zapis moz et soderzhat

* vremia
* mesto
* uchastnikov
* posledovatelnost
* povedenie
* travmu
* fotografii
* svidetelej
* cto bylo sdelano
* nuzhna li klinika
* zhalobu

**dlia kogo i dlia kakoj celi**

dlia xoziaev, organizatorov, moderatorov i pri neobxodimosti specialistov

**kakoj rezultat dolzen byt dostignut**

incident mozno rassmotret po faktam bez publicn oj travli odnogo pitomca ili xoziajina

---

## 70 — zavershenie ili pauza druzhby pitomcev

**cto eto dolzno delat**

xoziajin moz et

* zaversit druzhbu
* postavit na pauzu
* skryt iz profilia
* soxranit istoriceskuju sviaz
* ostavit xoziaev druzjami
* prekratit vse kontakty

**pocemu eto nuzno**

povedenie, mesto zitelstva i otnosheniia mogut izmenitsia

**kak eto dolzno rabotat po logike**

zavershenie druzhby pitomcev ne dolzno avtomaticeski blokirovat xoziaev, esli oni ne vybrali etot variant

**dlia kogo i dlia kakoj celi**

dlia gibkogo upravleniia socialnoj istoriej

**kakoj rezultat dolzen byt dostignut**

pitomcy mogut perestat vstrechatsia, ne razrushaja vse ostalnye socialnye sviazi sem ej

---

# rekomendacii druzej i profilej

## 71 — glavnaja cel rekomendacij

**cto eto dolzno delat**

sistema dolzna pomogat naxodit relevantnyx xoziaev, pitomcev, gruppy i profilej bez neobxodimosti znat tocnoe username

**pocemu eto nuzno**

novyj polzovatel moz et ne znat ni odnogo akkaunta, hotia v ego gorode est podxodiasc ee soobscestvo

**kak eto dolzno rabotat po logike**

rekomendacii dolzny osnovyvatsia na razresennyx signalax i pokaz yvat pon iatnuju prichinu

**dlia kogo i dlia kakoj celi**

dlia novyx polzovatelej, pereexavshix xoziaev, priutov, volonterov i pitomcev bez socialnyx sviazej

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet neskolko pon iatnyx i bezopasnyx predlozhenij vmesto slucajnogo spiska samyx populiarnyx akkauntov

---

## 72 — razreshennye signaly rekomendacij

**cto eto dolzno delat**

po soglasiju sistema moz et ucit yvat

* gorod
* obobschonn yj rajon
* vid pitomca
* vozrastn uju gruppu
* uroven aktivnosti
* interesy
* jazyki
* obsc ix druzej
* obsc ie gruppy
* uchastie v sobytijax
* socialnye celi
* predpoctitelnyj format obseniia

**pocemu eto nuzno**

bez relevantnyx signalov rekomendacii budut slucajnymi

**kak eto dolzno rabotat po logike**

kazdyj signal mozno otkliucit, a cuvstitelnye medicinskie dannye ne dolzny ispolzovatsia bez otdelnogo osnovaniia

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye xotiat personalizirovann yj poisk

**kakoj rezultat dolzen byt dostignut**

rekomendacii sootvetstvujut interesam, no ne raskryvajut diagnozy, adres ili gps

---

## 73 — obobschonnaja lokacija vmesto tocn oj

**cto eto dolzno delat**

algoritm moz et ispolzovat gorod, rajon ili pribliziteln yj radius

**pocemu eto nuzno**

tocn aja lokacija ne nuzhna dlia bazovoj rekomendacii profilej

**kak eto dolzno rabotat po logike**

polzovatel moz et videt

* v vashem gorode
* v sosednem rajone
* primerno v predelax ukazannogo radiusa
* dostup en tolko onlajn

ne nado pokaz yvat rasstojanie do metra

**dlia kogo i dlia kakoj celi**

dlia lokalnyx znakomstv pri zascite domashnego mesta

**kakoj rezultat dolzen byt dostignut**

polzovatel naxodit liudej riadom, no ne moz et po rekomendacii opredelit ix dom

---

## 74 — obsc ie druzia

**cto eto dolzno delat**

sistema moz et pokazat nalichie obsc ix socialnyx sviazej

**pocemu eto nuzno**

obsch ij znakom yj moz et povysit pon iatnost konteksta novogo profilia

**kak eto dolzno rabotat po logike**

pokazyvajutsia tolko te obsc ie druzia, kotorye razresili vidimost socialnyx sviazej

**dlia kogo i dlia kakoj celi**

dlia bezopasnogo otkrytiia profilej v socialnom graf e

**kakoj rezultat dolzen byt dostignut**

polzovatel vidit relevantn yj kontekst bez raskrytiia zakrytyx spiskov druzej

---

## 75 — obsc ie gruppy

**cto eto dolzno delat**

rekomendacija moz et objasniat, cto polzovateli sosto iat v odn oj gruppe

**pocemu eto nuzno**

obsch ij interes moz et byt bol ee vaznym, chem poroda ili populiarnost

**kak eto dolzno rabotat po logike**

zakrytaia, medicinskaia ili chuvstvitelnaia gruppa ne dolzna raskryvatsia bez prav

**dlia kogo i dlia kakoj celi**

dlia tematiceskix znakomstv

**kakoj rezultat dolzen byt dostignut**

polzovatel naxodit profilej po realnomu obschemu interesu, ne raskryvaja zakryt oe uchastie

---

## 76 — obsc ie sobytija

**cto eto dolzno delat**

sistema moz et rekomendovat profil, s kotorym polzovatel uze byl na odn om publicnom sobytii

**pocemu eto nuzno**

realn yj obsch ij opyt daet pon iatn yj kontekst dlia kontakta

**kak eto dolzno rabotat po logike**

uchastie v zakrytom ili chuvstvitelnom sobytii ne dolzno raskryvatsia postoronnim

**dlia kogo i dlia kakoj celi**

dlia prodolzeniia znakomstva posle progulki, treninga ili volonterskoj akc ii

**kakoj rezultat dolzen byt dostignut**

polzovatel vidit profilej, s kotorymi u nego uze byl bezopasn yj obsch ij kontekst

---

## 77 — socialnye celi

**cto eto dolzno delat**

rekomendacii dolzny ucit yvat, cto polzovatel ishchet

* druzej dlia pitomca
* druzej dlia obseniia
* gruppovye progulki
* volonterstvo
* onlajn obsch enie
* pomosc novickam
* nikakix novyx znakomstv

**pocemu eto nuzno**

dva profilej mogut byt poxozimi, no imet protivopolozhnye socialnye celi

**kak eto dolzno rabotat po logike**

profil so statusom ne ishchu znakomstv ne dolzen aktivno rekomendovatsia novym liudiam

**dlia kogo i dlia kakoj celi**

dlia uvazheniia tekusc ix granic polzovatelia

**kakoj rezultat dolzen byt dostignut**

algoritm ne predlagaet aktivn yj kontakt celoveku, kotoryj ego otkliucil

---

## 78 — rekomendacija po vidu pitomca

**cto eto dolzno delat**

sistema moz et predlagat profilej s tem ze ili relevantnym vidom

**pocemu eto nuzno**

xoziajie odnogo vida c asto imejut obsc ie temy, specialistov, mesta i zadaci

**kak eto dolzno rabotat po logike**

odinakov yj vid ne dolzen byt edinstvennym faktorom i ne dolzen avtomaticeski oznachat sovmestimost dlia realnoj vstrechi

**dlia kogo i dlia kakoj celi**

dlia tematiceskogo obseniia i obmena opytom

**kakoj rezultat dolzen byt dostignut**

xoziajin popugaja naxodit drugix xoziaev ptic, no ne polucaet lozn uju garantiju sovmestimosti samix ptic

---

## 79 — rekomendacija po urovniu aktivnosti

**cto eto dolzno delat**

po zhelaniju algoritm moz et sravnivat predpoctitelnyj temp aktivnosti

**pocemu eto nuzno**

aktivn yj begovoj pitomec i pozhiloj medlenn yj pitomec mogut iskat raznye formaty progulok

**kak eto dolzno rabotat po logike**

uroven aktivnosti dolzen byt tolko odnim iz signalov i ne dolzen ispolzovatsia kak medicinskaja ocenka

**dlia kogo i dlia kakoj celi**

dlia poiska partnerov po progulkam i aktivnosti

**kakoj rezultat dolzen byt dostignut**

rekomendovannye aktivnosti luchshe sootvetstvujut realnomu tempu pitomca

---

## 80 — rekomendacija po jazyku

**cto eto dolzno delat**

sistema moz et predlagat xoziaev, s kotorymi est obsch ij jazyk obseniia

**pocemu eto nuzno**

v mnogojazy cnom gorode pon iatn yj jazyk moz et byt vaznee drugix signalov

**kak eto dolzno rabotat po logike**

avtomaticeskij perevod moz et rasshirit rekomendacii, no polzovatel dolzen videt originaln yj jazyk profilia

**dlia kogo i dlia kakoj celi**

dlia mezhdunarodnyx i mestnyx soobscestv

**kakoj rezultat dolzen byt dostignut**

polzovateli mogut realno obschatsia, a ne tolko vizualno sovpadat po drugim priznakam

---

## 81 — novye profilej ne dolzny byt nevidimymi

**cto eto dolzno delat**

algoritm dolzen davat bezopasn uju vozmoznost novym realnym profiljam pojavljatsia v rekomendacijax

**pocemu eto nuzno**

esli rekomendujutsia tolko profilej s bolshim kolichestvom druzej, novyj polzovatel nikogda ne smozet sozdat socialn yj krug

**kak eto dolzno rabotat po logike**

mozno sozdat blok i

* novye v vashem gorode
* nedavno prisojedinilis k gruppe
* ishchut perv yx druzej
* novye proverennye profilej

**dlia kogo i dlia kakoj celi**

dlia novyx polzovatelej i novyx priutskix profilej

**kakoj rezultat dolzen byt dostignut**

socialnaja set ne prevrashch a etsia v zakryt yj krug uze populiarnyx akkauntov

---

## 82 — raznoobrazie rekomendacij

**cto eto dolzno delat**

sistema dolzna pokaz yvat ne tolko odin tip profilej

**pocemu eto nuzno**

slishkom uzkaja personalizacija sozdaet povtoriajusc ijsia inform acionn yj krug

**kak eto dolzno rabotat po logike**

rekomendacii mogut vkliucat

* mestnyx xoziaev
* xoziaev togo ze vida
* opytnyx volonterov
* novye profilej
* drugie jazyki s perevodom
* onlajn soobscestva
* profilej s raznymi, no sovmestimymi interesami

**dlia kogo i dlia kakoj celi**

dlia rasshireniia poleznyx socialnyx sviazej

**kakoj rezultat dolzen byt dostignut**

polzovatel ne vidit desiatki prakticeski odinakov yx profilej

---

## 83 — pocemu eto rekomendovano

**cto eto dolzno delat**

riadom s kazdym predlozheniem dolzna byt kratkaia prichina

naprimer

* vy v odn oj gruppe
* nashi pitomcy poxozego vozrasta
* ishchet spokojny e progulki
* govorit na vashem jazyke
* zhivet v vashem gorode
* u vas est obsch ij drug

**pocemu eto nuzno**

skrytaia rekomendacija bez objasneniia moz et vygl iadet kak slezhka

**kak eto dolzno rabotat po logike**

objasnenie ne dolzno raskryvat chuvstiteln yj signal

nelzia pisat rekomendovan iz-za diagnoza ili tocn oj lokacii

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej rekomendacionnoj sistemy

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet logiku i moz et resit, nuzhen li emu takoi tip predlozhenij

---

## 84 — skryt rekomendaciju

**cto eto dolzno delat**

polzovatel moz et ubrat konkretn yj profil iz predlozhenij

**pocemu eto nuzno**

ne kazdoe algoritmiceskoe sovpadenie interesno ili umestno

**kak eto dolzno rabotat po logike**

mozno vybrat prichinu

* ne interesno
* uze znakom y
* ne xocu etot tip profilej
* slishkom daleko
* ne podxodit pitomcu
* ne pokaz yvat etot akkaunt
* ne pokaz yvat po etomu signalu

**dlia kogo i dlia kakoj celi**

dlia kontro lia personalizacii

**kakoj rezultat dolzen byt dostignut**

odin i tot ze nezhelateln yj profil ne vozvrashchaetsia postojanno v rekomendacii

---

## 85 — ne rekomendovat moj profil

**cto eto dolzno delat**

xoziajin moz et ubrat sebia ili pitomca iz avtomaticeskix socialnyx rekomendacij

**pocemu eto nuzno**

profil moz et byt nuzhen dlia medkartocki, gps ili grupp, no ne dlia novyx znakomstv

**kak eto dolzno rabotat po logike**

profil ostajotsia dostupnym po priamoj ssylke v ramkax privatnosti, no ne pojavliaetsia v blokax predlozennyx druzej

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye ne xotiat byt algoritmiceski obnaruzhennymi

**kakoj rezultat dolzen byt dostignut**

prakticeskie funkcii dostupny bez obyazatelnoj socialnoj vidimosti

---

## 86 — ne ispolzovat cuvstitelnye medicinskie dannye

**cto eto dolzno delat**

rekomendacionnaja sistema ne dolzna ispolzovat diagnozy, lekarstva, analiz y ili straxovye dannye dlia oby cnyx socialnyx rekomendacij

**pocemu eto nuzno**

eto cuvstitelnaja informacija, kotoraja ne nuzhna dlia bazovogo poiska druzej

**kak eto dolzno rabotat po logike**

xoziajin moz et dobrovolno vstupit v zakrytuju tematiceskuju gruppu, no ego diagnoz ne dolzen avtomaticeski raskryvatsia cerez predlozhenie druzej

**dlia kogo i dlia kakoj celi**

dlia zascity medicinskoj privatnosti pitomcev i xoziaev

**kakoj rezultat dolzen byt dostignut**

drugoj polzovatel ne moz et dogadatsia o diagnoze pitomca po tomu, kakie profilej emu rekomendujutsia

---

## 87 — reklamnye rekomendacii

**cto eto dolzno delat**

platnoe prodvizhenie profilia, gruppy ili organizacii dolzno byt jasno otmecheno

**pocemu eto nuzno**

polzovatel ne dolzen prinimat reklamu za obyektivnoe socialnoe sovpadenie

**kak eto dolzno rabotat po logike**

riadom pokazyvaetsia

* reklama
* sponsor
* platnoe prodvizhenie
* pocemu vy popali v auditoriju v obobschonnom vide

**dlia kogo i dlia kakoj celi**

dlia biznesov, specialistov, priutov i polzovatelej, kotorye vidat rekomendaciju

**kakoj rezultat dolzen byt dostignut**

kommerceskoe prodvizhenie ne maskiruetsia pod lichn uju rekomendaciju druzhby

---

## 88 — rekomendacii i blokirovki

**cto eto dolzno delat**

zablokirovannye, ogranichennye i udalennye profilej ne dolzny rekomendovatsia drug drugu

**pocemu eto nuzno**

algoritm ne dolzen vozvrashchat problemn yj kontakt posle osoznannoj blokirovki

**kak eto dolzno rabotat po logike**

blokirovka proveriaetsia do sozdaniia rekomendacii, a ne tolko posle ejo otobrazheniia

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, zascischajusc ix svoi granicy

**kakoj rezultat dolzen byt dostignut**

zablokirovann yj akkaunt ne pojavliaetsia v druz iah, mestnyx predlozhenijax i blokax vozobnovit kontakt

---

## 89 — rekomendacii posle otklonennogo zaprosa

**cto eto dolzno delat**

profil, zapros ot kotorogo byl otklonen, ne dolzen postojanno vozvrashchatsia v rekomendacii

**pocemu eto nuzno**

eto sozdaet davlenie i obxodit reshenie polzovatelia

**kak eto dolzno rabotat po logike**

posle otkloneniia predlozhenie skryvaetsia na dliteln yj period ili polnostiu, v zavisimosti ot nast roek

**dlia kogo i dlia kakoj celi**

dlia poluchatelej, kotorye uze vyrazili otsutstvie interesa

**kakoj rezultat dolzen byt dostignut**

algoritm uvazaet socialn oe net

---

# poisk profilej

## 90 — osnovnoj poisk

**cto eto dolzno delat**

polzovatel moz et iskat

* xoziajina
* pitomca
* specialista
* priut
* organizaciju
* grup pu
* sobytie

**pocemu eto nuzno**

rekomendacii ne zameniajut celenapravlenn yj poisk konkretnogo profilia

**kak eto dolzno rabotat po logike**

poisk dolzen ponimat username, publicnoe imia, alternativnye napisaniia i dopustimye opечатki

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye uze znajut, kogo ili cto oni ishchut

**kakoj rezultat dolzen byt dostignut**

konkretn yj publicn yj profil naxoditsia bez neobxodimosti znat tocn uju ssylku

---

## 91 — poisk po username

**cto eto dolzno delat**

poisk po unikalnomu imeni dolzen byt samym tocn ym sposobom

**pocemu eto nuzno**

publicnye imena mogut sovpadat

**kak eto dolzno rabotat po logike**

tochn oe sovpadenie username pokaz yvaetsia vyshe, no zakryt yj profil vse ravno podciniaetsia privatnosti

**dlia kogo i dlia kakoj celi**

dlia poiska konkretnogo znakomogo ili profilia po vizitke

**kakoj rezultat dolzen byt dostignut**

polzovatel ne putajet dva odinakov yx imeni

---

## 92 — poisk po imeni pitomca

**cto eto dolzno delat**

mozno iskat publicnye profilej po imeni i alternativnym imenam

**pocemu eto nuzno**

xoziajin moz et znat imia pitomca, no ne ego username

**kak eto dolzno rabotat po logike**

rezultaty dopolniajutsia bezopasnym kontekstom

* vid
* avatar
* gorod
* xoziajin v dopustimom vide
* obsch ie sviazi

**dlia kogo i dlia kakoj celi**

dlia poiska uze znakom yx pitomcev

**kakoj rezultat dolzen byt dostignut**

odinakov y e imena ne privodiat k slucajnomu kontaktu s ne tem profilem

---

## 93 — filtry po vidu i porode

**cto eto dolzno delat**

poisk moz et filtrovat profilej po vidu, podvidu, porode ili metisam

**pocemu eto nuzno**

polzovatel moz et iskat tematiceskoe soobscestvo ili druzej dlia opredelennogo vida

**kak eto dolzno rabotat po logike**

poroda ukazannaja xoziajinom i podtverzhdennaja dokumentom dolzny imet raznye metki, no oba varianta mogut byt dostupny v poisk e

**dlia kogo i dlia kakoj celi**

dlia porodnyx, vidovyx i obrazovatelnyx soobscestv

**kakoj rezultat dolzen byt dostignut**

poisk otrazaet urov en tochnosti, a ne vydajot predpolozhenie za dokazann yj fakt

---

## 94 — filtry po socialnoj celi

**cto eto dolzno delat**

mozno iskat profilej, kotorye

* ishchut druzej
* ishchut gruppovye progulki
* dostupny tolko onlajn
* xotiat volonterit
* gotovy pomogat novickam
* ne ishchut novyx kontaktov

**pocemu eto nuzno**

socialnaja gotovnost vaznee prostogo nalichija profilia

**kak eto dolzno rabotat po logike**

v rezultate pokazyvajutsia tolko profilej, razreshivshie takoi tip obnaruzheniia

**dlia kogo i dlia kakoj celi**

dlia poiska ljudej s sovpadajusc imi namerenijami

**kakoj rezultat dolzen byt dostignut**

polzovatel ne p ytaetsia sviazatsia s profilem, kotoryj zaranee zakryl novye znakomstva

---

## 95 — filtr po mestu

**cto eto dolzno delat**

mozno vybrat

* moj gorod
* moj rajon
* primern yj radius
* sosednie goroda
* vsia strana
* tolko onlajn

**pocemu eto nuzno**

raznye socialnye celi imejut razn uju geografiju

**kak eto dolzno rabotat po logike**

rezultat ne dolzen pokaz yvat tocn oe rasstojanie, esli polzovatel ego ne razresil

**dlia kogo i dlia kakoj celi**

dlia lokalnyx progulok i mezhdunarodnogo onlajn obseniia

**kakoj rezultat dolzen byt dostignut**

polzovatel naxodit podxodiascuju geografiju bez utecki adresa

---

## 96 — filtr po jazyku

**cto eto dolzno delat**

polzovatel moz et iskat xoziaev po predpoctitelnomu jazyku obseniia

**pocemu eto nuzno**

obsch ij jazyk vazen dlia bezopasnogo soglasovaniia vstrechi i instrukcij o pitomce

**kak eto dolzno rabotat po logike**

mozno vybrat

* svobodno
* bazovoe obsch enie
* pismenn oe obsch enie
* avtomaticeskij perevod
* konkret n yj jazyk

**dlia kogo i dlia kakoj celi**

dlia mnogojazy cnyx gorodov i mezhdunarodnyx grupp

**kakoj rezultat dolzen byt dostignut**

polzovateli mogut ponimat drug druga pri obsuzhdenii granic i bezopasnosti

---

## 97 — filtr po formatu obseniia

**cto eto dolzno delat**

mozno ukazat

* lichnye vstrechi
* gruppovye progulki
* onlajn chat
* video obsch enie
* tematiceskie gruppy
* volonterstvo
* sovmestnye treningi
* bez priamyx soobsenij

**pocemu eto nuzno**

ne vse polzovateli xotiat odnogo i togo ze formata

**kak eto dolzno rabotat po logike**

filtr osnovyvaetsia na dobrovolno ukazann oj socialnoj dostupnosti

**dlia kogo i dlia kakoj celi**

dlia poiska podxodiascego sposoba kontakta

**kakoj rezultat dolzen byt dostignut**

onlajn polzovatel ne polucaet tolko predlozheniia fiziceskix vstrech

---

## 98 — transliteracija i raznye alfavity

**cto eto dolzno delat**

poisk dolzen ponimat raznye napisaniia imeni

naprimer kirillicu, latinicu i transliteraciju

**pocemu eto nuzno**

odin profil moz et byt izvesten pod raznymi napisaniiami

**kak eto dolzno rabotat po logike**

poisk moz et ispolzovat normalizovannye varianty, no ne dolzen sam izmeniat publicnoe imia

**dlia kogo i dlia kakoj celi**

dlia mnogojazy cnyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

profil naxoditsia daze pri drugom dopustimom napisanii imeni

---

## 99 — zascita ot perebora profilej

**cto eto dolzno delat**

sistema ne dolzna pozvoliat massovo proveryat, sushestvuet li profil po spisku telefonov, email ili drugih lichnyx identifikatorov

**pocemu eto nuzno**

eto moz et ispolzovatsia dlia presledovaniia i sbora dannyx

**kak eto dolzno rabotat po logike**

poisk po kontaktu rabotaet tolko po soglasi iu vladelca i ne raskryvaet sam kontakt

massovye zaprosy ogranichivajutsia

**dlia kogo i dlia kakoj celi**

dlia zascity socialnoj privatnosti vseh polzovatelej

**kakoj rezultat dolzen byt dostignut**

zlo umyshlennik ne moz et zagruzit bazu nomerov i poluchit spisok profil ej

---

## 100 — soxranenn yj poisk

**cto eto dolzno delat**

polzovatel moz et soxranit nabor filtrov

naprimer

`spokojnye sobaki srednego razmera dlia parallel n yx progulok v vilniuse`

**pocemu eto nuzno**

podxodiasc ij profil moz et pojavitcia ne srazu

**kak eto dolzno rabotat po logike**

polzovatel vybiraet

* tolko soxranit
* uvedomliat srazu
* svodka raz v nedeliu
* tolko novye profilej
* ne uvedomliat

**dlia kogo i dlia kakoj celi**

dlia cel enapravlennogo dolgosrocnogo poisk a

**kakoj rezultat dolzen byt dostignut**

polzovatel ne povtoriaet odni i te ze filtry kazdyj den

---

# soobsenija i nacalo kontakta

## 101 — zapros na soobsenie

**cto eto dolzno delat**

neznakom yj polzovatel dolzen snachala otpravit zapros na dialog, esli priamye soobsenija ne razreseny

**pocemu eto nuzno**

otkrytyj vxodiasc ij chat privodit k spamu, domogatelstvu i fishingu

**kak eto dolzno rabotat po logike**

zapros soderzit

* otpravitelia
* profil, ot imeni kotorogo on pishet
* kontekst
* korotkoe soobsenie
* obsc ie sviazi
* knopku prin iat
* otklonit
* zablokirovat
* pozhalovatsia

**dlia kogo i dlia kakoj celi**

dlia novyx socialnyx i professionalnyx kontaktov

**kakoj rezultat dolzen byt dostignut**

neznakom oe soobsenie ne popadaet srazu v osnovnoj lichn yj chat

---

## 102 — kontekst dialoga

**cto eto dolzno delat**

chat dolzen pokaz yvat, pocemu dialog byl nacat

* zapros druzhby
* pitomec
* gruppa
* sobytie
* usluga
* marketplace
* adopcija
* nabludenie poterjannogo pitomca

**pocemu eto nuzno**

odin polzovatel moz et obschatsia s tem ze celovekom po raznym temam

**kak eto dolzno rabotat po logike**

kontekst moz et byt sviazan s kartockoj, no lichn yj dialog ne dolzen avtomaticeski polucat vse dannye etogo obekta

**dlia kogo i dlia kakoj celi**

dlia xoziaev, specialistov, pokupatelej, volonterov i organizatorov

**kakoj rezultat dolzen byt dostignut**

obe storony ponimajut, k kakomu pitomcu, sobytiju ili usluge otnositsia obsch enie

---

## 103 — kto realno pishet ot imeni pitomca

**cto eto dolzno delat**

vnutrenne i pri neobxodimosti publicno dolzen byt vid en realn yj upravliajusc ij

**pocemu eto nuzno**

pitomec ne moz et samostojatelno otvechat za ugrozy, sdelki ili soglasiia

**kak eto dolzno rabotat po logike**

mozno pokazat

`andrej ot imeni baksa`

v ob y cnom tvorceskom chat e imia upravliajuscego moz et byt skryto vizualno, no vsegda xranitsia v audit e

**dlia kogo i dlia kakoj celi**

dlia moderacii, sporov, marketplace i professionalnyx voprosov

**kakoj rezultat dolzen byt dostignut**

nelzia izbezhat otvetstvennosti, skryvayas za profilem pitomca

---

## 104 — ogranichenie pervogo soobsenija

**cto eto dolzno delat**

perv oe soobsenie neznakomomu moz et imet limit po dline, ssylkam i vlozhenijam

**pocemu eto nuzno**

moshenniki c asto nac inajut s dlinnogo reklam nogo teksta, podozritelnoj ssylki ili falshivogo dokumenta

**kak eto dolzno rabotat po logike**

do prin iatiia dialoga mozno razreshit tolko

* korotkij tekst
* kartocku profilia
* bezopasn uju kartocku sobytija
* fotografiju pri zayavlennom nabludenii
* bez aktivnyx vneshnix ssylok

**dlia kogo i dlia kakoj celi**

dlia zascity poluchatelia do ustanovleniia doverija

**kakoj rezultat dolzen byt dostignut**

podozriteln yj akkaunt ne moz et srazu otpravit bolshoj arhiv, prilozhenie ili plateznuju ssylku

---

## 105 — predlozhennye shablony pervogo soobsenija

**cto eto dolzno delat**

platforma moz et predlozhit neytralnye varianty

* my byli na odn oj progulke
* xocu utochnit naschet sledujuscego sobytija
* nashi pitomcy poxozhe liubiat spokojny e progulki
* ja uvidel vashe objavlenie v gruppe
* xocu zadat vopros po adopcii

**pocemu eto nuzno**

shablon pomogaet novicku nacat obsuzhdenie uvazhitelno i po teme

**kak eto dolzno rabotat po logike**

tekst mozno izmenit, a shablon ne dolzen avtomaticeski otpravljatsia bez podtverzhdeniia

**dlia kogo i dlia kakoj celi**

dlia novyx i neopytnyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

perv oe obrashenie stanovitsia pon iatnee i reze vygl iadit kak spam

---

## 106 — ssylki v soobsenijax

**cto eto dolzno delat**

pered otkrytiem vneshnej ssylki sistema dolzna pokazat domen i preduprezhdenie

**pocemu eto nuzno**

fishing moz et imit irovat platezn uju stranicu, podderzhku, kliniku ili servis dostavki

**kak eto dolzno rabotat po logike**

opasnye domeny blokirujutsia, podozritelnye trebujut dopolnitelnoe dejstvie, a sistemnye ssylki pokazyvajutsia kak proverennye

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej chat a

**kakoj rezultat dolzen byt dostignut**

polzovatel ne popadaet na poddeln uju stranicu odnim slucajnym nazhatiem

---

## 107 — plateznye kody i paroli

**cto eto dolzno delat**

sistema dolzna preduprezhdat, cto nelzia peredavat

* parol
* mfa kod
* kod bankovskoj operacii
* poln yj nomer karty
* kod vosstanovleniia
* kod peredaci vladen iia
* kod podtverzhdeniia dostavki do realnogo polucheniia

**pocemu eto nuzno**

moshenniki mogut predstavliatsia podderzhkoj, priutom ili specialistom

**kak eto dolzno rabotat po logike**

pri rasp oznanii takogo zaprosa sistema moz et pokazat zametn oe preduprezhdenie i knopku zhaloby

**dlia kogo i dlia kakoj celi**

dlia zascity ot socialnoj inzenerii

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet, cto realnaja podderzhka ne dolzna prosit takoi kod v chat e

---

## 108 — foto i video v pervom dialoge

**cto eto dolzno delat**

media mozno ogranichit do prin iatiia dialoga

**pocemu eto nuzno**

neznakom yj akkaunt moz et otpravit shokirujusc ij, seksualn yj, moshenniceskij ili zhestokij kontent

**kak eto dolzno rabotat po logike**

predprosmotr moz et byt skryt do dobrovolnogo otkrytija, a chuvstviteln oe media prohodit avtomaticeskuju i rucnuju proverku po zhalobe

**dlia kogo i dlia kakoj celi**

dlia poluchatelej zaprosov na dialog

**kakoj rezultat dolzen byt dostignut**

neznakom yj celovek ne moz et nemedlenno pokazat poluchateliu nezhelateln oe izobrazhenie

---

## 109 — otkliuchenie statusa prochteniia

**cto eto dolzno delat**

polzovatel moz et skryt, procital li on soobsenie

**pocemu eto nuzno**

status prochteniia moz et sozda vat socialn oe davlenie ili ispolzovatsia pri presledovanii

**kak eto dolzno rabotat po logike**

nast roika moz et primeniatsia

* ko vsem
* k neznakomym
* k ogranichennym profiljam
* tolko k konkretnym dialogam

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorym nuzno bolshe kontrolia nad komunikaciej

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et procitat soobsenie bez obyazatelnogo nemedlennogo otveta

---

## 110 — zvonki

**cto eto dolzno delat**

audio ili videozvonok dolzen byt dostup en tolko po soglasi iu i v ramkax nast roek

**pocemu eto nuzno**

vnezapny e zvonki ot neznakom yx polzovatelej mogut byt navi azchivymi ili opasnymi

**kak eto dolzno rabotat po logike**

mozno vybrat

* tolko druzia
* tolko po dogovorennosti
* tolko aktivnye uslugi
* nikto
* konkretnye iskliuceniia

**dlia kogo i dlia kakoj celi**

dlia xoziaev, specialistov, sitterov i organizatorov

**kakoj rezultat dolzen byt dostignut**

neznakom yj polzovatel ne moz et neozhidanno nacat videozvonok

---

## 111 — zavershenie dialoga

**cto eto dolzno delat**

polzovatel moz et

* arhivirovat
* skryt
* otklonit novye soobsenija
* ogranichit
* zablokirovat
* udal it svoju kopiju
* pozhalovatsia

**pocemu eto nuzno**

raznye situacii trebu jut raznogo urovnia dejstviia

**kak eto dolzno rabotat po logike**

udaleniie lichnoj kopii ne dolzno unichtozhat dokazatelstva zhaloby ili cuzuju kopiju bez pravilnogo processa

**dlia kogo i dlia kakoj celi**

dlia upravleniia vxodiasc imi i bezopasnostju

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et prekratit kontakt i pri neobxodimosti soxranit dokazatelstva narusheniia

---

# blokirovka, ogranichenie i skrytie

## 112 — polnaja blokirovka

**cto eto dolzno delat**

blokirovka dolzna prekrashchat

* prosmotr zakrytogo profilia
* lichnye soobsenija
* zaprosy
* zvonki
* upominanija
* priglasheniia
* rekomendacii
* vidimost statusa v seti
* dostup k novym publikacijam
* vrem enn yj obmen lokaciej

**pocemu eto nuzno**

blokirovka javliaetsia osnovnym instrumentom zascity ot presledovaniia i nezhelatelnogo kontakta

**kak eto dolzno rabotat po logike**

pravila proveriajutsia na servere, a ne tolko skryvajut knopki

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej socialnoj seti

**kakoj rezultat dolzen byt dostignut**

zablokirovann yj akkaunt ne moz et prodolzhat kontakt cerez oby cnye funkcii platformy

---

## 113 — blokirovka odnogo profilia ili vsego akkaunta

**cto eto dolzno delat**

xoziajin moz et vybrat

* zablokirovat konkretnogo pitomca
* zablokirovat lichn yj profil
* zablokirovat ves akkaunt
* zablokirovat vse upravliaemye im profilej
* zablokirovat professionalnye obrasheniia

**pocemu eto nuzno**

u odnogo celoveka mogut byt neskolko pitomcev i rolej, cerez kotorye on moz et obojti ogranichenie

**kak eto dolzno rabotat po logike**

interfejs dolzen pon iatno objasnit masshtab blokirovki

**dlia kogo i dlia kakoj celi**

dlia zascity ot obxoda cerez drugie profilej

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et polnostiu prekratit kontakt s problemnym celovekom, a ne tolko s odnim avatarom

---

## 114 — ogranichenie profilia

**cto eto dolzno delat**

ogranichenn yj profil moz et ostavatsia v druz iah, no ego vzaimodejstviia stanov iatsia menee zametnymi

**pocemu eto nuzno**

polnaja blokirovka ne vsegda nuzhna ili bezopasna v konfliktnoj situacii

**kak eto dolzno rabotat po logike**

posle ogranicheniia

* soobsenija idut v otdeln yj razdel
* status prochteniia skryt
* kommentarii mogut ozhidat odobreniia
* upominanija ne vyz yvajut signal
* zvonki blokirujutsia
* rekomendacii prekrashchajutsia

**dlia kogo i dlia kakoj celi**

dlia myagkogo kontrolia problemnogo kontakta

**kakoj rezultat dolzen byt dostignut**

vliianie nezhelatelnogo akkaunta snizaetsia bez publicn oj eskalacii

---

## 115 — skrytie ili mute

**cto eto dolzno delat**

polzovatel moz et ne videt kontent profilia, ne razryvaja socialnuju sviaz

**pocemu eto nuzno**

drug moz et publikovat slishkom mnogo kontenta ili vrem enno neinteresn uju temu

**kak eto dolzno rabotat po logike**

mozno skryt

* vse posty
* tolko istorii
* tolko video
* tolko reklamn yj kontent
* uvedomlenija
* konkretn uju temu
* na opredelenn yj period

**dlia kogo i dlia kakoj celi**

dlia upravleniia lichn oj lentoj

**kakoj rezultat dolzen byt dostignut**

polzovatel snizaet informacionn uju nagruzku bez obyazatelnogo udaleniia druga

---

## 116 — udaleniie iz druzej

**cto eto dolzno delat**

liubaia storona moz et zaversit dvustoronnju druzhbu

**pocemu eto nuzno**

socialnye otnosheniia mogut izmenitsia

**kak eto dolzno rabotat po logike**

posle udaleniia

* zakryt yj kontent dlia druzej bolshe ne dostup en
* dialog moz et ostatcia ili byt arhivirovan
* druzhba pitomcev moz et obrabatyvatsia otdelno
* obsc ie albomy soxraniajut svoi prava
* rekomendacii vrem enno ne pokazyvajut profil

**dlia kogo i dlia kakoj celi**

dlia dobrovolnogo upravleniia socialnym grafom

**kakoj rezultat dolzen byt dostignut**

druzhba moz et byt zavershena bez polnoj blokirovki i bez udaleniia vsej istorii

---

## 117 — skrytaia blokirovka pri risk e presledovaniia

**cto eto dolzno delat**

sistema ne dolzna obyazatelno soobscat problemnomu akkauntu, cto ego zablokirovali

**pocemu eto nuzno**

priam oe uvedomlenie moz et vyzvat sozdanie novogo akkaunta ili druguju eskalaciju

**kak eto dolzno rabotat po logike**

zablokirovann yj polzovatel vidit nejtraln yj rezultat

* profil nedostup en
* kontent ne naiden
* dejstvie nedostupno

**dlia kogo i dlia kakoj celi**

dlia polzovatelej v situacii presledovaniia ili ugrozy

**kakoj rezultat dolzen byt dostignut**

blokirovka zasciscaet, ne davaja dopolnitelnoj informacii obidciku

---

## 118 — zascita ot obxoda blokirovki

**cto eto dolzno delat**

sistema dolzna vyiavliat vozmoznyj obxod cerez novye akkaunty, profilej pitomcev, organizacii ili gruppovye priglasheniia

**pocemu eto nuzno**

prostoe sozdanie novogo profilia ne dolzno polnostiu obnul iat zascitu

**kak eto dolzno rabotat po logike**

mozno ucit yvat

* upravliajusc ij akkaunt
* proverennye kontakty
* ustrojstva
* povedenceskie signaly
* odinakov y e soobsenija
* zhaloby

okonchatelnoe reshenie vaznyx slucaev dolzno imet rucn uju proverku

**dlia kogo i dlia kakoj celi**

dlia zascity ot sistematiceskogo presledovaniia

**kakoj rezultat dolzen byt dostignut**

odin celovek ne moz et beskonecno sozda vat novye profilej dlia prodolzeniia kontakta

---

# nesovershennoletnie i semejnaja bezopasnost

## 119 — zascita nesovershennoletnix profilej

**cto eto dolzno delat**

akkaunty nesovershennoletnix dolzny imet usilennye ogranicheniia socialnyx sviazej

**pocemu eto nuzno**

socialnaja set s geolokaciej, vstrechami i marketplace moz et byt ispolzovana vzroslymi dlia opasnogo kontakta

**kak eto dolzno rabotat po logike**

po umolcaniju

* profil zakryt
* zaprosy ot neznakom yx ogranicheny
* tocn aja lokacija nedostupna
* videozvonki ot neznakom yx zapreshcheny
* lichnye vstrechi trebu jut ucastiia vzroslogo
* marketplace ogranichen
* semejn yj upravliajusc ij polucaet vaznye signal y

**dlia kogo i dlia kakoj celi**

dlia detej, podrostkov i ix zakonnyx predstavitelej

**kakoj rezultat dolzen byt dostignut**

nesovershennoletnij moz et ucavstvovat v bezopasnyx tematiceskix funkcii ax bez nekontroliruemogo kontakta so vzroslymi

---

## 120 — vstrecha s uchastiem nesovershennoletnego

**cto eto dolzno delat**

sistema dolzna trebovat prisutstvie ili odobrenie vzroslogo dlia realn oj vstrechi

**pocemu eto nuzno**

nesovershennoletnij ne dolzen odin vstrecatsia s neznakom ym vzroslym po povodu pitomca

**kak eto dolzno rabotat po logike**

priglashenie napravliaetsia semejnomu upravliajuscemu, a mesto dolzno byt publicnym i pon iatnym

**dlia kogo i dlia kakoj celi**

dlia semejnyx akkauntov

**kakoj rezultat dolzen byt dostignut**

socialnaja funkcia ne sozdaet priam oj i skrytyj kanal dlia opasn oj lichn oj vstrechi

---

## 121 — skrytie vozrasta rebenka

**cto eto dolzno delat**

publicno mozno pokaz yvat tolko vozrastn uju kategoriju ili nichego

**pocemu eto nuzno**

tocn aja data rozdenija i vozrast mogut ispolzovatsia dlia moshennicestva i presledovaniia

**kak eto dolzno rabotat po logike**

platforma znaet neobxodim uju vozrastnuju kategoriju dlia prav, no ne publik uet ejo bez prichiny

**dlia kogo i dlia kakoj celi**

dlia zascity detej i podrostkov

**kakoj rezultat dolzen byt dostignut**

bezopasnostnye pravila rabotajut, no postoronnij ne polucaet lishnjuju lichnuju informaciju

---

# gruppovye socialnye sviazi

## 122 — priglashenie v grup pu

**cto eto dolzno delat**

drug, xoziajin ili organizator moz et priglasit polzovatelia ili profil pitomca v grup pu

**pocemu eto nuzno**

gruppy javliajutsia osnovnym sposobom tematiceskogo obseniia

**kak eto dolzno rabotat po logike**

priglashenie dolzno pokaz yvat

* kto priglashaet
* kakaja gruppa
* publicnaia ili zakrytaia
* pravila
* pocemu polzovatel poluchil priglashenie
* cto budet vidno posle vstup leniia

**dlia kogo i dlia kakoj celi**

dlia mestnyx, tematiceskix, porodnyx, professionalnyx i volonterskix grupp

**kakoj rezultat dolzen byt dostignut**

polzovatel prinimaet reshenie, ponimaja auditoriju i pravila gruppy

---

## 123 — zapret massovyx priglashenij

**cto eto dolzno delat**

odin akkaunt ne dolzen beskontrolno priglashat tysiachi neznakom yx polzovatelej

**pocemu eto nuzno**

gruppy mogut ispolzovatsia dlia reklamnogo spama, politiceskoj agitacii, moshennicestva ili travli

**kak eto dolzno rabotat po logike**

limit zavisit ot

* roli
* razmera gruppy
* istorii organizatora
* procenta prin iatiia
* zhalob
* obsc ix sviazej
* tipa gruppy

**dlia kogo i dlia kakoj celi**

dlia zascity polzovatelej ot massovogo spama

**kakoj rezultat dolzen byt dostignut**

priglasheniia ostajutsia relevantnymi, a ne prevrashchajutsia v reklamn uju rassylku

---

## 124 — vstup lenie ot imeni xoziajina ili pitomca

**cto eto dolzno delat**

polzovatel dolzen vybrat, kakoi profil vstu p a et v grup pu

**pocemu eto nuzno**

v professionaln uju grup pu moz et vstupat specialist, a v grup pu progulok konkretn yj pitomec

**kak eto dolzno rabotat po logike**

gruppa opredeliaet, kakie tipy profilej dopustimy

realn yj upravliajusc ij vsegda fiksiruetsia

**dlia kogo i dlia kakoj celi**

dlia polzovatelej s neskolkimi roliami i pitomcami

**kakoj rezultat dolzen byt dostignut**

odin akkaunt moz et ucavstvovat v raznyx gruppax v pravilnom kontekste bez sozdaniia dublikatov

---

## 125 — druzhba posle gruppovogo obseniia

**cto eto dolzno delat**

uchastniki gruppy mogut otpravit zapros drug drugu, esli nast roiki eto razreshajut

**pocemu eto nuzno**

obsch aja gruppa sozdaet pon iatn yj kontekst, no ne dolzna avtomaticeski otkryvat lichnye soobsenija vsem uchastnikam

**kak eto dolzno rabotat po logike**

zapros pokaz yvaet obsch uju grup pu, no ne raskryvaet drugie zakrytye gruppy

**dlia kogo i dlia kakoj celi**

dlia postepennogo perehoda ot gruppovogo k lichnomu obsch eniju

**kakoj rezultat dolzen byt dostignut**

uchastniki mogut prodolzhit relevantn yj kontakt bez avtomaticeskogo otkrytiia lichnyx chat ov

---

# priglasheniia na progulki i aktivnosti

## 126 — priglashenie na progulku

**cto eto dolzno delat**

drug ili znakom yj moz et predlozhit sovmestn uju progulku

**pocemu eto nuzno**

socialnaja sviaz dolzna privodit k poleznym realnym aktivnostiam, esli obe storony etogo xotiat

**kak eto dolzno rabotat po logike**

priglashenie soderzit

* pitomcev
* datu
* vremia
* mesto
* primern uju prodolzhitelnost
* temp
* kolichestvo uchastnikov
* osobye uslovija
* plan pri ploxoj pogode

**dlia kogo i dlia kakoj celi**

dlia druzej pitomcev i mestnyx xoziaev

**kakoj rezultat dolzen byt dostignut**

progulka organizuetsia strukturirovanno, a ne ter iaetsia v dlinn oj perepiske

---

## 127 — dostupnost dlia progulok

**cto eto dolzno delat**

xoziajin moz et ukazat obsch uju dostupnost

* utrom
* dnem
* vecerom
* po vyxodnym
* tolko po dogovorennosti
* vrem enno nedostup en
* ne ishchu progulki

**pocemu eto nuzno**

predlozheniia v neudobnoe vremia sozdajut lishnjuju nagruzku

**kak eto dolzno rabotat po logike**

eto ne dolzno raskryvat tocn oe ezhednevn oe raspisanie ili vremia otsutstviia doma

**dlia kogo i dlia kakoj celi**

dlia podbora primerno podxodiascego vremeni bez utecki raspisaniia semji

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet predlozheniia v realno vozmozny e period y

---

## 128 — tip aktivnosti

**cto eto dolzno delat**

priglashenie moz et byt na

* spokojnuju progulku
* parallel n uju progulku
* aktivn yj marshrut
* trening
* poiskovuju igru
* plavanie
* fotosessiju
* poezdku
* onlajn obsch enie
* domashnjuju vstrechu tolko mezhdu uze doverennymi liudmi

**pocemu eto nuzno**

slovo vstrecha slishkom obs ch ee i ne pokaz yvaet nagruzku i format

**kak eto dolzno rabotat po logike**

tip vliiaet na podgotovku, filtr y i instrukcii

**dlia kogo i dlia kakoj celi**

dlia podbora aktivnosti po pitomcu i socialnoj celi

**kakoj rezultat dolzen byt dostignut**

xoziajin ponimaet, cto imenno predlagaetsia do prin iatiia priglasheniia

---

## 129 — gruppovaja progulka

**cto eto dolzno delat**

neskolko xoziaev mogut sozdat ogranicenn uju gruppovuju aktivnost

**pocemu eto nuzno**

mestnye soobscestva c asto organizujut reguliarnoe sovmestn oe vremia

**kak eto dolzno rabotat po logike**

nuzno ukazat

* maksimalnoe kolichestvo
* tip pitomcev
* pravila
* temp
* mesto sbora
* organizatora
* ogranicheniia
* plan otmeny
* kontakt pri opozdanii

**dlia kogo i dlia kakoj celi**

dlia mestnyx soobscestv i druzeskix grupp

**kakoj rezultat dolzen byt dostignut**

gruppa ne stanovitsia nekontroliruemoj tolpoj s nepon iatnym kolichestvom i raznymi ozhidaniiami

---

## 130 — proverka prav mesta

**cto eto dolzno delat**

pered progulkoj sistema moz et pokazat pravila mesta

* nuzhen li povodok
* razresheny li zivotnye
* est li ogranicheniia po vremeni
* est li og razhdenie
* est li voda
* est li dostupnost
* est li opasnye zony

**pocemu eto nuzno**

ne vse mesta podxodiat dlia vseh formatov aktivnosti

**kak eto dolzno rabotat po logike**

informacija imeet datu poslednej proverki i moz et byt obnovlena soobscestvom ili organizaciej

**dlia kogo i dlia kakoj celi**

dlia organizatorov i uchastnikov vstrech

**kakoj rezultat dolzen byt dostignut**

uchastniki ne uznajut na meste, cto sobaki zapreshcheny ili povodok obyazatelen

---

## 131 — vrem ennaia gruppa vstrechi

**cto eto dolzno delat**

dlia konkretnoj progulki moz et sozda v atcia vrem ennyj chat

**pocemu eto nuzno**

uchastnikam nuzno koordinirivat mesto, opozdanie i izmeneniia, no postojannaia gruppa ne vsegda nuzhna

**kak eto dolzno rabotat po logike**

posle sobytija chat moz et

* arhivirovatsia
* udal it vrem ennye lokacii
* predlozhit sozdat postojann uju grup pu
* soxranit fotografii po soglasiju
* zakryt novye soobsenija

**dlia kogo i dlia kakoj celi**

dlia razovyx i polupublicnyx aktivnosti

**kakoj rezultat dolzen byt dostignut**

koordinacionn yj chat ne ostajotsia bessrocno aktivnym bez neobxodimosti

---

## 132 — povtoriajusc iesia vstrechi

**cto eto dolzno delat**

druzia ili gruppa mogut sozdat reguliarnoe raspisanie

**pocemu eto nuzno**

postojannye partner y po progulkax ne dolzny dogovarivatsia s nulia kazdyj raz

**kak eto dolzno rabotat po logike**

mozno ustanovit

* dni
* primernoe vremia
* mesto
* uchastnikov
* podtverzhdenie kazd oj vstrechi
* pauzu
* sezonn oe raspisanie
* avtomaticeskuju otmen u pri nekotoryx uslovijax

**dlia kogo i dlia kakoj celi**

dlia stabilnyx druzeskix grupp

**kakoj rezultat dolzen byt dostignut**

reguliarnoe obsch enie stanovitsia udobnym, no ne raskryvaetsia publicno kak raspisanie odsutstviia doma

---

# vidimost socialnogo grafa

## 133 — spisok druzej

**cto eto dolzno delat**

xoziajin moz et nastroit vidimost druzej

* vse
* tolko druzia
* tolko obsc ie druzia
* tolko kolichestvo
* nichego
* tolko upravliajusc ie profilia

**pocemu eto nuzno**

socialn yj graf raskryvaet semejnye, mestnye i professionalnye sviazi

**kak eto dolzno rabotat po logike**

skrytyj spisok ne dolzen raskryvatsia cerez api, podskazki, eksport ili poisk

**dlia kogo i dlia kakoj celi**

dlia vseh xoziaev i profilej pitomcev

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et imet socialn yj krug bez publicacii polnoj karty sviazej

---

## 134 — obsc ie druzia pri skrytom spiske

**cto eto dolzno delat**

polzovatel moz et razresit pokaz tolko obsc ix druzej

**pocemu eto nuzno**

eto daet kontekst, ne otkryvaja ves spisok

**kak eto dolzno rabotat po logike**

obsch ij drug pokazyvaetsia tolko togda, kogda ego sobstvennye nastojki eto dopuskajut

**dlia kogo i dlia kakoj celi**

dlia novyx znakomstv

**kakoj rezultat dolzen byt dostignut**

socialn yj kontekst vid en bez narusheniia privatnosti tretjego celoveka

---

## 135 — skrytye socialnye sviazi

**cto eto dolzno delat**

nekotorye sviazi mogut byt nepublicnymi

* semejnye
* professionalnye
* medicinskie
* zakryt y e gruppy
* adopcionnye kandidaty
* ekstrennye kontakty
* sporn y e sviazi

**pocemu eto nuzno**

sam fakt sviazi moz et raskryvat cuvstiteln uju informaciju

**kak eto dolzno rabotat po logike**

takaja sviaz ispolzuetsia funkcion alno, no ne pokaz yvaetsia na publicnom profile

**dlia kogo i dlia kakoj celi**

dlia zascity sem ej, klientov specialistov, priutov i xoziaev

**kakoj rezultat dolzen byt dostignut**

postoronnij ne moz et po spisku sviazej dogadatsia o lechenii, adopcionnom spore ili semejnom konflikte

---

## 136 — schetciki druzej i podpiscikov

**cto eto dolzno delat**

kolichestvo druzej i podpiscikov dolzno ucit yvat realnye aktivnye sviazi

**pocemu eto nuzno**

udalennye, zablokirovannye, falshiv y e i priostanovlennye profilej ne dolzny iskazhat cifry

**kak eto dolzno rabotat po logike**

raznye zriteli mogut videt razn yj schetcik v zavisimosti ot privatnosti i blokirovok

**dlia kogo i dlia kakoj celi**

dlia posledovatelnosti profilia i analitiki

**kakoj rezultat dolzen byt dostignut**

cifry sovpadajut vo vsex razdelax i ne raskryvajut skrytye sviazi

---

# uvedomlenija

## 137 — uvedomlenie o novom zaprose

**cto eto dolzno delat**

poluchatel moz et polucit signal o novom zaprose na druzhbu, podpis ku ili dialog

**pocemu eto nuzno**

bez uvedomleniia zapros moz et ostatcia nezamechenn ym

**kak eto dolzno rabotat po logike**

uvedomlenie pokaz yvaet

* profil
* tip zaprosa
* kontekst
* obsc ie sviazi
* bezopasnye dejstviia

cuvstitelnye dannye ne pokazyvajutsia na zablokirovannom ekrane bez razresheniia

**dlia kogo i dlia kakoj celi**

dlia poluchatelej socialnyx zaprosov

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et bystro ocenit zapros bez otkrytija polnogo prilozheniia

---

## 138 — uvedomlenie o prin iatii

**cto eto dolzno delat**

otpravitel moz et uznat, cto zapros prin iat

**pocemu eto nuzno**

eto podtverzhdaet sozdanie socialnoj sviazi

**kak eto dolzno rabotat po logike**

uvedomlenie moz et predlozhit

* napisat
* posmotret profil
* priglasit na progulku
* nast roit uvedomlenija
* nichego ne delat

**dlia kogo i dlia kakoj celi**

dlia novyx druzej

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet, cto kontakt sozd an, no ne cuvstvuet davleniia nemedlenno pisat

---

## 139 — ne uvedomliat o kazdom udal enii iz druzej

**cto eto dolzno delat**

po umolcaniju sistema ne dolzna otpravljat dramaticeskoe uvedomlenie o tom, cto polzovatel udal en iz druzej ili podpiscikov

**pocemu eto nuzno**

takoe uvedomlenie moz et vyzvat konflikt, presledovanie ili nenuzn y e voprosy

**kak eto dolzno rabotat po logike**

izmenenie prosto otrazaetsia v status e sviazi

iskliuceniem mogut byt administrativnye ili professionalnye sviazi, gde nuzno objasnenie prav

**dlia kogo i dlia kakoj celi**

dlia socialnyx druzej i podpiscikov

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et tixo izmenit svoj socialn yj krug

---

## 140 — svodka socialnyx uvedomlenij

**cto eto dolzno delat**

vmesto desiatkov otdelnyx push mozno polucat svodku

`u vas tri novyx zaprosa, dva priglasheniia na progulku i odno novoe soobsenie`

**pocemu eto nuzno**

slishkom mnogo signalov zastavliaet polzovatelia otkliucit ix polnostiu

**kak eto dolzno rabotat po logike**

srocn y e bezopasnostnye sobytija ne obed injajutsia s oby cnymi socialnymi reakciiami

**dlia kogo i dlia kakoj celi**

dlia aktivnyx polzovatelej s bolshim socialnym krugom

**kakoj rezultat dolzen byt dostignut**

socialnye uvedomlenija ostajutsia poleznymi i ne prevrashchajutsia v postojann yj shum

---

## 141 — tixie casy

**cto eto dolzno delat**

zaprosy, podpiski i oby cnye priglasheniia ne dolzny budit polzovatelia nochju

**pocemu eto nuzno**

socialnaja aktivnost redko trebuet nemedlennogo nocnogo otveta

**kak eto dolzno rabotat po logike**

iskliuceniia mogut byt

* aktivn yj poisk pitomca
* srocn oe izmenenie sobytija
* ekstrennyj kontakt
* neposredstvennaia ugroza bezopasnosti

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej

**kakoj rezultat dolzen byt dostignut**

polzovatel otd yxaet bez socialnogo shuma, no ne propuskaet realno srocn yj signal

---

# moderacija i zhaloby

## 142 — zhaloba na nezhelateln yj zapros

**cto eto dolzno delat**

poluchatel moz et pozhalovatsia na zapros po prichinam

* spam
* reklama
* seksualn oe domogatelstvo
* presledovanie
* moshennicestvo
* ugroza
* falshiv yj profil
* vydaca za drugogo
* sobiraet lichnye dannye
* drugaja pricina

**pocemu eto nuzno**

prostoe otklonenie ne zasciscaet drugih polzovatelej ot togo ze akkaunta

**kak eto dolzno rabotat po logike**

zhaloba moz et avtomaticeski zablokirovat dalnejs ij kontakt dlia zhalobscika i peredat dokazatelstva moderatoru

**dlia kogo i dlia kakoj celi**

dlia zascity poluchatelej i vsego soobscestva

**kakoj rezultat dolzen byt dostignut**

problemn yj akkaunt ne prodolzaet massovo otpravljat odinakov y e obrasheniia

---

## 143 — zhaloba na presledovanie

**cto eto dolzno delat**

polzovatel moz et otpravit edinuju zhalobu, vkliucajuscuju neskolko profilej i kanalov obxoda blokirovki

**pocemu eto nuzno**

presledovatel moz et ispolzovat lichn yj profil, profilej pitomcev, gruppy, kommentarii i novye akkaunty

**kak eto dolzno rabotat po logike**

sistema sobiraet sviazann y e dokazatelstva

* zaprosy
* soobsenija
* blokirovki
* novye akkaunty
* upominanija
* priglasheniia
* popytki poluchit lokaciju

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorye stolknulis s sistematiceskim davleniem

**kakoj rezultat dolzen byt dostignut**

moderacija rassmatrivaet obsch uju kartinu, a ne kazdyj novyj akkaunt kak nesviazann yj incident

---

## 144 — zhaloba na moshennicestvo

**cto eto dolzno delat**

mozno soobscit, cto polzovatel

* prosit kod
* prosit oplatit vne platformy
* vydajot sebia za podderzhku
* predlagaet falshiv uju uslugu
* soobscaet lozhn oe nabludenie pitomca
* prosit lichnye dokumenty
* otpravliaet fishing ssylku

**pocemu eto nuzno**

socialnye sviazi c asto ispolzujutsia dlia formirovaniia lozhnogo doverija

**kak eto dolzno rabotat po logike**

pri dostatocnom risk e sistema moz et

* skryt ssylku
* ogranichit soobsenija
* ostanovit platezi
* predupredit drugih adresatov
* zaprosit proverku akkaunta
* soxranit dokazatelstva

**dlia kogo i dlia kakoj celi**

dlia zascity xoziaev, priutov, volonterov i pokupatelej

**kakoj rezultat dolzen byt dostignut**

moshenniceskaia sxema ostanavlivaetsia do rasprostraneniia na bolshoe kolichestvo polzovatelej

---

## 145 — lozhn y e zhaloby

**cto eto dolzno delat**

platforma dolzna vyiavliat sistematiceskie lozhn y e ili koordinir ovannye zhaloby

**pocemu eto nuzno**

zhaloby mogut ispolzovatsia dlia mest i, konkurencii ili travli

**kak eto dolzno rabotat po logike**

odin otklonenn yj spor ne dolzen avtomaticeski oznachat lozhn uju zhalobu

nuzna proverka povtoriajusc egosia povedeniia, dokazatelstv i koordinacii

**dlia kogo i dlia kakoj celi**

dlia zascity realnyx polzovatelej i specialistov ot zloupotrebleniia moderaciej

**kakoj rezultat dolzen byt dostignut**

sistema zasciscaet zhalobscikov, no ne pozvoliaet massovo blokirovat nevinn y e profilej

---

## 146 — koordinir ovannaia travlia

**cto eto dolzno delat**

platforma dolzna zam ec at massovye odnovremennye zaprosy, upominanija, negativnye kommentarii i zhaloby protiv odnogo profilia

**pocemu eto nuzno**

gruppa polzovatelej moz et organizovat ataku iz-za lichnogo konflikta

**kak eto dolzno rabotat po logike**

pri podozrenii mozno

* vrem enno skryt massovye upominanija
* ogranichit novye kommentarii
* zamedlit zhaloby
* proverit istochnik
* zascitit kontaktne dannye
* predostavit cel evomu akkauntu centr bezopasnosti

**dlia kogo i dlia kakoj celi**

dlia profilej xoziaev, specialistov, priutov i populiarnyx pitomcev

**kakoj rezultat dolzen byt dostignut**

massovaia ataka ne moz et bystro unictozhit reputaciju ili zablokirovat profil bez proverki

---

## 147 — moderacionnye dejstviia

**cto eto dolzno delat**

moderator moz et

* ogranichit zaprosy
* ogranichit soobsenija
* vrem enno zablokirovat novye kontakty
* skryt profil iz rekomendacij
* udal it opasnoe soobsenie
* zablokirovat akkaunt
* zablokirovat vse upravliaemye profilej
* zaprosit proverku lichnosti
* vosstanovit oshibocno ogranichenn yj akkaunt

**pocemu eto nuzno**

polnaja blokirovka ne vsegda sootvetstvuet konkretnomu narusheniju

**kak eto dolzno rabotat po logike**

dejstvie dolzno byt proportionalnym, imet srok, osnovanie i vozmoznost apellacii

**dlia kogo i dlia kakoj celi**

dlia komandy bezopasnosti i polzovatelej, zatronutyx resheniem

**kakoj rezultat dolzen byt dostignut**

vred ostanavlivaetsia, no odna oshibka ne obyazatelno udal iaet ves akkaunt i profilej pitomcev

---

## 148 — objasnenie resheniia

**cto eto dolzno delat**

polzovatel dolzen polucit

* cto bylo ogranicheno
* po kakomu pravilu
* na kakoi srok
* cto mozno ispravit
* mozno li podat apellaciju
* kakie funkcii ostajutsia dostupnymi

**pocemu eto nuzno**

nepon iatnaia blokirovka ne pomogaet izmenit povedenie i snizaet doverie

**kak eto dolzno rabotat po logike**

lichnost zhalobscika i chuvstiteln y e dokazatelstva ne raskryvajutsia narushiteliu

**dlia kogo i dlia kakoj celi**

dlia spravedlivogo moderacionnogo processa

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet granicy resheniia i moz et ego korrektno osparivat

---

## 149 — apellacija

**cto eto dolzno delat**

polzovatel moz et osparivat ogranichenie socialnyx funkcii

**pocemu eto nuzno**

algoritm ili moderator moz et oshibitsia

**kak eto dolzno rabotat po logike**

apellacija moz et soderzhat

* objasnenie
* kontekst
* dokumenty
* skrinshoty
* ssylku na sobytie
* svidetelej
* novye dokazatelstva
* zapros proverki drugim moderatorom

**dlia kogo i dlia kakoj celi**

dlia zascity ot oshibocnyx reshenij

**kakoj rezultat dolzen byt dostignut**

realn yj polzovatel moz et vosstanovit socialnye funkcii bez poteri profilia pitomca i istorii

---

# anti fraud i falshiv y e profilej

## 150 — falshiv yj profil pitomca

**cto eto dolzno delat**

sistema dolzna vyiavliat profilej, sozda nnye iz ukradennyx fotografij ili vymyslennyx dannyx

**pocemu eto nuzno**

falshiv y e pitomcy mogut ispolzovatsia dlia moshennicestva, nakrutki, sbora deneg i manipuliacii konkursami

**kak eto dolzno rabotat po logike**

signaly

* fotografii iz drugix profilej
* mnogo novyx profilej odnogo akkaunta
* net logiceskoj istorii
* odinakov y e bio
* massovye zaprosy
* poddelnye dokumenty
* protivorechiv y e dannye

**dlia kogo i dlia kakoj celi**

dlia zascity vsego socialnogo grafa

**kakoj rezultat dolzen byt dostignut**

falshiv yj profil ne moz et bystro sobrat druzej i ispolzovat ix doverie

---

## 151 — vydaca za izvestnogo pitomca ili xoziajina

**cto eto dolzno delat**

platforma dolzna zascischat publicnye profilej ot vizualn oj poddelki

**pocemu eto nuzno**

moshennik moz et skopirovat avatar, imia, bio i username s odnoj izmenenn oj bukvoj

**kak eto dolzno rabotat po logike**

nuzno

* proveriat poxozie username
* rezervirovat sistemnye nazvaniia
* pokaz yvat podrobnosti proverki
* razresat zhalobu na imitaciju
* skryvat podozriteln yj profil do proverki pri vysokom risk e

**dlia kogo i dlia kakoj celi**

dlia izvestnyx profilej, priutov, specialistov i oby cnyx xoziaev

**kakoj rezultat dolzen byt dostignut**

drugie polzovateli legche otlicajut original ot poddelki

---

## 152 — kuplennye druzia i vzaimnye nakrutki

**cto eto dolzno delat**

sistema dolzna zam ec at set i akkauntov, kotorye massovo dobavljajut drug druga radi cifr

**pocemu eto nuzno**

iskusstvenn yj socialn yj graf iskazhaet rekomendacii, doverie i konkursy

**kak eto dolzno rabotat po logike**

podozritelnye sviazi mogut ne ucit yvatsia v rejtingax do proverki

**dlia kogo i dlia kakoj celi**

dlia chestn oj popularnosti i reklamn oj analitiki

**kakoj rezultat dolzen byt dostignut**

kolichestvo druzej ne stanovitsia prostym pokupaemym pokazatelem

---

## 153 — podozriteln y e zaprosy o lokacii

**cto eto dolzno delat**

sistema dolzna preduprezhdat, esli neznakom yj polzovatel nastojcivo prosit

* domashnij adres
* tocn uju gps tochku
* reguliarnoe vremia progulki
* mesto xraneniia dorogogo pitomca
* kod dveri
* vremia otsutstviia doma

**pocemu eto nuzno**

takie dannye mogut ispolzovatsia dlia presledovaniia ili krazhi

**kak eto dolzno rabotat po logike**

soobsenie moz et poluchit risk metku, a poluchateliu predlagaetsia ne otvechat, zablokirovat ili pozhalovatsia

**dlia kogo i dlia kakoj celi**

dlia fiziceskoj bezopasnosti xoziaev i pitomcev

**kakoj rezultat dolzen byt dostignut**

polzovatel ne peredaet cuvstiteln uju lokaciju pod vidom oby cnogo socialnogo voprosa

---

# tehniceskaia logika

## 154 — socialnaja sviaz kak otdelnyj obekt

**cto eto dolzno delat**

kazdaia sviaz dolzna xranit

* perv yj profil
* vtoroj profil
* tip sviazi
* napravlenie
* status
* avtora zaprosa
* vremia
* kontekst
* privatnost
* prava
* istoriju
* prichinu zaversheniia pri neobxodimosti

**pocemu eto nuzno**

prostoe pole friend_ids ne podderzhit podpiski, pitomcev, professionalnye sviazi, blokirovki i vremennye kontakty

**kak eto dolzno rabotat po logike**

odin profil moz et imet raznye odnovremennye sviazi s drugim profilem

naprimer xoziajie druzia, pitomcy znakom y po parku, a odin xoziajin esio i sitter

**dlia kogo i dlia kakoj celi**

dlia stabilnoj arhitektury vsej socialnoj seti

**kakoj rezultat dolzen byt dostignut**

novyj tip sviazi mozno dobavit bez peredelki vsego socialnogo grafa

---

## 155 — napravlennye i vzaimnye sviazi

**cto eto dolzno delat**

sistema dolzna razdeliat

* odnostoronn uju podpis ku
* dvustoronnju druzhbu
* odnostoronnju blokirovku
* vzaimn oe sovladenie
* napravlenn oe professionalnoe napravlenie

**pocemu eto nuzno**

ne vse socialnye otnosheniia simmetricny

**kak eto dolzno rabotat po logike**

napravlenie javliaetsia c astiu tipa sviazi i ne vyvoditsia iz dvux nesviazannyx zapisej bez kontrolia

**dlia kogo i dlia kakoj celi**

dlia korrektn oj logiki podpisok, zaprosov i blokirovok

**kakoj rezultat dolzen byt dostignut**

otpiska odnogo polzovatelia ne udal iaet podpis ku drugogo v obratn uju storonu

---

## 156 — unikalnost aktivnoj sviazi

**cto eto dolzno delat**

mezhdu dvumia konkretnymi profiljami ne dolzno byt neskolko odinakov yx aktivnyx sviazej odnogo tipa

**pocemu eto nuzno**

povtorn oe nazhatie ili odnovremennye dejstviia sovladelcev mogut sozdat dublikaty

**kak eto dolzno rabotat po logike**

unikalnost proveriaetsia pri sozdanii i povtorn oj obrabotke zaprosa

**dlia kogo i dlia kakoj celi**

dlia stabiln oj raboty pri medlennom internete i konkurentnyx dejstviiax

**kakoj rezultat dolzen byt dostignut**

odin zapros sozdaet odnu druzhbu, a ne neskolko odinakov yx zapisej

---

## 157 — idempotentnost socialnyx dejstvij

**cto eto dolzno delat**

povtorn oe nazhatie ne dolzno dva raza

* otpravit zapros
* prin iat
* otklonit
* zablokirovat
* razblokirovat
* podpisatsia
* otpisatsia
* priglasit na vstrechu

**pocemu eto nuzno**

mobilnaja set moz et medlenno otvechat

**kak eto dolzno rabotat po logike**

kazdoe kriticeskoe dejstvie polucaet unikaln yj identifikator

**dlia kogo i dlia kakoj celi**

dlia vseh polzovatelej i ustojcivosti platformy

**kakoj rezultat dolzen byt dostignut**

interfejs i baza vsegda pokaz yvajut odin predskazuemyj rezultat

---

## 158 — odnovremenn oe prin iatie i otmena

**cto eto dolzno delat**

sistema dolzna korrektno obrabotat situaciju, kogda odna storona prinimaet zapros v tot ze moment, kogda drugaia ego otmeniaet

**pocemu eto nuzno**

bez kontrolia mogut pojavitcia protivorechiv y e statusy

**kak eto dolzno rabotat po logike**

okonchatelnoe reshenie dolzno opiratsia na posledovatelno proverenn yj status, a obe storony polucajut aktualn yj rezultat

**dlia kogo i dlia kakoj celi**

dlia stabilnosti socialnogo grafa

**kakoj rezultat dolzen byt dostignut**

ne pojavliaetsia druzhba, kotoraja u odn oj storony est, a u drugoj net

---

## 159 — audit socialnyx dejstvij

**cto eto dolzno delat**

sistema dolzna xranit vaznye dejstviia

* kto otpravil zapros
* ot imeni kakogo profilia
* kto prin iat
* kto udal il druzhbu
* kto dobavil v blizkij krug
* kto zablokiroval
* kto razblokiroval
* kto izmenil tip sviazi
* kto vydal vrem enn yj dostup

**pocemu eto nuzno**

pri neskolkix upravliajusc ix i sporax nuzno znat realnogo avtora

**kak eto dolzno rabotat po logike**

audit ne obyazatelno publicn yj, no dostup en vladelcu i moderatoru po osnovaniju

**dlia kogo i dlia kakoj celi**

dlia xoziaev, priutov, organizacij i moderacii

**kakoj rezultat dolzen byt dostignut**

mozno ustanovit, kak imenno pojav ilas ili ischezla socialnaja sviaz

---

## 160 — dinamiceskaia proverka prav

**cto eto dolzno delat**

dostup k kontentu dolzen proveriatsia pri kazdom otkrytii, a ne tolko v moment sozdaniia druzhby

**pocemu eto nuzno**

privatnost, blokirovka, rol ili status profilia mogut izmenitsia

**kak eto dolzno rabotat po logike**

sistema proveriaet

* tekusc uju sviaz
* blokirovki
* status akkaunta
* status profilia
* auditoriju kontenta
* vrem enn yj srok
* individualnye iskliuceniia

**dlia kogo i dlia kakoj celi**

dlia realn oj zascity zakrytogo kontenta

**kakoj rezultat dolzen byt dostignut**

udalenn yj drug ne moz et otkryt st ar y j zakryt yj post po soxranenn oj ssylke

---

## 161 — invalidacija kesa posle blokirovki

**cto eto dolzno delat**

posle blokirovki nuzno bystro obnovit

* profil
* lentu
* chat
* rekomendacii
* poisk
* spiski druzej
* upominanija
* priglasheniia
* predprosmotry
* lokaln yj kesh prilozheniia

**pocemu eto nuzno**

blokirovka bespolezna, esli st araia kopija esio dostupna

**kak eto dolzno rabotat po logike**

bezopasnostnye izmeneniia imejut vysokij prioritet obnovleniia

**dlia kogo i dlia kakoj celi**

dlia zascity polzovatelia posle nemedlennogo resheniia

**kakoj rezultat dolzen byt dostignut**

zablokirovann yj profil ischezaet iz dostupnyx razdelov bez dlinn oj zaderzhki

---

## 162 — poiskovyj indeks i privatnost

**cto eto dolzno delat**

poisk dolzen indeksirovat tolko profilej i polia, razreshennye dlia obnaruzheniia

**pocemu eto nuzno**

zakryt yj profil moz et slucajno raskrytsia cerez podskazku poisk a

**kak eto dolzno rabotat po logike**

pri izmenenii privatnosti, blokirovke ili deaktivacii indeks obnovliaetsia

**dlia kogo i dlia kakoj celi**

dlia bezopasnogo poiska profilej

**kakoj rezultat dolzen byt dostignut**

skrytyj profil ne pojavliaetsia v rezultat ax i podskazkax postoronnemu polzovateliu

---

## 163 — schetciki i blokirovki

**cto eto dolzno delat**

schetciki druzej, podpiscikov i obsc ix kontaktov dolzny ucit yvat blokirovki i prava zritel ia

**pocemu eto nuzno**

odno i to ze publicnoe chislo moz et kosvenno raskryvat skryt uju sviaz

**kak eto dolzno rabotat po logike**

pri neobxodimosti pokaz yvaetsia obobschonn oe ili okruglenn oe znachenie

**dlia kogo i dlia kakoj celi**

dlia zascity socialnogo grafa

**kakoj rezultat dolzen byt dostignut**

po izmenenii schetcika nelzia legko vychislit, kto imenno zablokiroval ili udal ilsia

---

## 164 — profil peredan novomu xoziajinu

**cto eto dolzno delat**

peredaca pitomca dolzna pravilno obrabotat ego socialnye sviazi

**pocemu eto nuzno**

novyj xoziajin moz et ne xotet soxranit vse star y e druzhby, chat y i gruppy

**kak eto dolzno rabotat po logike**

pri peredace mozno vybrat

* soxranit druzej pitomca
* priostanovit socialnye sviazi
* skryt star y e dialogi
* soxranit publicn y e posty
* zaprosit povtorn oe podtverzhdenie nekotoryx sviazej
* udal it dostup predyduscego xoziajina

**dlia kogo i dlia kakoj celi**

dlia adopcii, smeny doma i peredaci mezhdu sem iami

**kakoj rezultat dolzen byt dostignut**

socialnaja istoria ne ter iaetsia avtomaticeski, no novyj xoziajin polucaet kontrol nad buduscej privatnostju

---

## 165 — obedinenie dublikatov profilej

**cto eto dolzno delat**

pri obed inenii profilej pitomca nuzno obed init ili razreshit konflikty socialnyx sviazej

**pocemu eto nuzno**

u dvux dublikatov mogut byt raznye druzia, podpisciki i blokirovki

**kak eto dolzno rabotat po logike**

nuzno

* obed init unikalnye podpiski
* ne sozda vat dublikat y druzej
* soxranit bolee stroguju blokirovku
* pokazat konflikty
* soxranit audit
* uvedomit upravliajusc ix pri neobxodimosti

**dlia kogo i dlia kakoj celi**

dlia ispravleniia dublikatov bez poteri socialnoj istorii

**kakoj rezultat dolzen byt dostignut**

posle obed ineniia ne pojavliajutsia dvojn y e druz ia ili vozobnovlenn y e zablokirovannye kontakty

---

## 166 — deaktivacija akkaunta

**cto eto dolzno delat**

pri vrem enn oj deaktivacii socialnye sviazi dolzny soxranitsia, no aktivn oe vzaimodejstvie priostanavlivatsia

**pocemu eto nuzno**

polzovatel moz et vernutsia pozze i ne xotet ter iat socialn yj krug

**kak eto dolzno rabotat po logike**

profil

* skryvaetsia iz rekomendacij
* ne prinimaet novye zaprosy
* ne pokaz yvaet status v seti
* soxraniaet druzej
* moz et ostavit srocnye funkc ii pitomca drugomu sovladelcu

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, kotorym nuzna pauza

**kakoj rezultat dolzen byt dostignut**

socialnaia istoria soxraniaetsia, no profil vrem enno ne ucavstvuet v novyx kontaktax

---

## 167 — udaleniie akkaunta

**cto eto dolzno delat**

pri polnom udaleni i nuzno pravilno obrabotat

* druzej
* podpiscikov
* chat y
* gruppy
* blizkij krug
* priglasheniia
* professionalnye sviazi
* profilej pitomcev
* aktivnye vstrechi

**pocemu eto nuzno**

prostoe udaleniie odn oj stroki moz et ostavit slomann y e sviazi i profilej bez upravliajuscego

**kak eto dolzno rabotat po logike**

pered udaleniem sistema predlagaet peredat profilej pitomcev, zaversit aktivnye uslugi i eksportirovat dannye

**dlia kogo i dlia kakoj celi**

dlia polzovatelej, pokidajusc ix platformu

**kakoj rezultat dolzen byt dostignut**

socialn yj graf ochiscaetsia predskazuemo, a pitomcy ne ostajutsia bez upravleniia

---

# mobilnaja versija

## 168 — mobiln yj ekran socialnyx sviazej

**cto eto dolzno delat**

na telefone dolzny byt dostupny

* novye zaprosy
* druzia
* podpisciki
* podpiski
* blizkij krug
* rekomendacii
* priglasheniia
* blokirovki
* poisk

**pocemu eto nuzno**

bolshinstvo socialnyx dejstvij proisxodit na telefone

**kak eto dolzno rabotat po logike**

vaznye dejstviia dolzny byt dostupny odnoj rukoj, no slucajn oe prin iatie ili blokirovka trebu jut pon iatnogo podtverzhdeniia

**dlia kogo i dlia kakoj celi**

dlia mobilnyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et upravliat kontaktami bez poisk a nuzn oj funkcii v glubokom meniu

---

## 169 — bystr y e dejstviia s zaprosom

**cto eto dolzno delat**

iz kartocki zaprosa mozno

* prin iat
* otklonit
* posmotret profil
* ogranichit
* zablokirovat
* pozhalovatsia

**pocemu eto nuzno**

polzovatel dolzen bystro reagirovat na pon iatn yj zapros

**kak eto dolzno rabotat po logike**

kriticeskie dejstviia ne dolzny byt raspolozheny slishkom blizko bez podtverzhdeniia

**dlia kogo i dlia kakoj celi**

dlia bezopasnogo mobilnogo upravleniia

**kakoj rezultat dolzen byt dostignut**

polzovatel ne blokiruet ili ne prinimaet zapros slucajnym kasaniem

---

## 170 — vidzhet novyx zaprosov

**cto eto dolzno delat**

vidzhet moz et pokaz yvat kolichestvo novyx zaprosov bez lichnyx podrobnostej

**pocemu eto nuzno**

na zablokirovannom ekrane ne nado raskryvat imia, fotografiju i kontekst kontakta bez soglasiia

**kak eto dolzno rabotat po logike**

polzovatel vybiraet uroven predprosmotra

* tolko kolichestvo
* imia
* tip zaprosa
* poln yj predprosmotr
* nichego

**dlia kogo i dlia kakoj celi**

dlia polzovatelej s raznym urovnem privatnosti telefona

**kakoj rezultat dolzen byt dostignut**

uvedomlenie polezno, no ne raskryvaet socialnye kontakty postoronnemu

---

## 171 — slaboe soedinenie

**cto eto dolzno delat**

prilozhenie dolzno korrektno obrabatyvat otpravku zaprosa pri nestabilnom internet e

**pocemu eto nuzno**

povtorn oe nazhatie moz et sozdat dublikaty ili protivorechiv y e statusy

**kak eto dolzno rabotat po logike**

interfejs pokaz yvaet

* otpravliaetsia
* dostavleno
* ne udalos
* povtorit
* zapros uze sushestvuet

**dlia kogo i dlia kakoj celi**

dlia polzovatelej mobilnoj seti

**kakoj rezultat dolzen byt dostignut**

odin zapros ne sozdaetsia neskolko raz iz-za medlennogo interneta

---

# desktop versija

## 172 — panel socialnogo grafa

**cto eto dolzno delat**

na kompiutere mozno pokazat

* spisok druzej
* zaprosy
* podpiscikov
* podpiski
* grup py
* sobytija
* rekomendacii
* filtry
* blokirovki
* istoriju sviazej

**pocemu eto nuzno**

pri bolshom kolichestve kontaktov mobiln yj spisok stanovitsia neudobnym

**kak eto dolzno rabotat po logike**

polzovatel moz et filtrovat po profilem, tipu sviazi, pitomcu, gruppe, gorodu i statusu

**dlia kogo i dlia kakoj celi**

dlia aktivnyx polzovatelej, priutov, organizacij i specialistov

**kakoj rezultat dolzen byt dostignut**

bolsh oj socialn yj graf ostajotsia upravljaemym

---

## 173 — massovoe upravlenie bez opasnyx dejstvij

**cto eto dolzno delat**

mozno massovo

* otkliucit uvedomlenija
* perenesti v kategoriju
* skryt kontent
* arhivirovat star y e zaprosy

no ne nado prostym dejstviem massovo peredavat prava ili otkryvat privatnost

**pocemu eto nuzno**

massovye funkcii udobny, no povysajut risk bolsh oj oshibki

**kak eto dolzno rabotat po logike**

kriticeskie dejstviia trebujut individualnogo podtverzhdeniia ili detalnogo predprosmotra

**dlia kogo i dlia kakoj celi**

dlia polzovatelej s bolshim socialnym krugom

**kakoj rezultat dolzen byt dostignut**

upravljanje stanovitsia bystr ym bez massovogo slucajnogo otkrytiia dannyx

---

# dostupnost

## 174 — ekrannye diktory

**cto eto dolzno delat**

kazdaia kartocka socialnoj sviazi dolzna citatcia kak

* imia
* tip profilia
* tip zaprosa
* obsc ij kontekst
* status
* dostupnye dejstviia

**pocemu eto nuzno**

odin avatar i ikonka bez teksta ne dajut pon iatnogo konteksta polzovateliu s narushenijami zreniia

**kak eto dolzno rabotat po logike**

knopki imejut polnye nazvaniia

naprimer prin iat zapros ot profilia baks, a ne prosto prin iat

**dlia kogo i dlia kakoj celi**

dlia samostojatelnogo upravleniia socialnymi kontaktami

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et bezopasno ocenit i obrabotat zapros bez vizualnoj pomosci

---

## 175 — upravlenie klaviaturoj

**cto eto dolzno delat**

bez myshi dolzny rabotat

* poisk
* filtry
* prosmotr profilia
* otpravka zaprosa
* prin iatie
* otklonenie
* blokirovka
* zhaloba
* nastojki vidimosti

**pocemu eto nuzno**

eto neobxodimo dlia dostupnosti i bystroj desktop raboty

**kak eto dolzno rabotat po logike**

poriadok fokusa dolzen byt predskazuemym, a dialog podtverzhdeniia ne dolzen ter iat fokus

**dlia kogo i dlia kakoj celi**

dlia polzovatelej s motor nymi ogranichenijami i klaviaturnogo upravleniia

**kakoj rezultat dolzen byt dostignut**

ves socialn yj cikl dostup en bez myshi

---

## 176 — status ne tolko cvetom

**cto eto dolzno delat**

statusy dolzny imet tekst i ikonku

* ozhidaet
* prin iat
* otklonen
* zablokirovan
* ogranichen
* blizkij krug
* vrem ennyj kontakt
* iste k

**pocemu eto nuzno**

cvet sam po sebe moz et byt nepon iaten ili nevid en

**kak eto dolzno rabotat po logike**

odin status odinakovo naz yvaetsia vo vseh razdelax

**dlia kogo i dlia kakoj celi**

dlia dostupnosti i posledovatelnosti

**kakoj rezultat dolzen byt dostignut**

polzovatel ponimaet status bez neobxodimosti razlicat cveta

---

## 177 — krupn yj tekst

**cto eto dolzno delat**

spiski, kartocki i knopki dolzny korrektno rabotat pri uvelichenii teksta

**pocemu eto nuzno**

dlinnye imena profilej, porody i kontekst zaprosa mogut obrezat vazn uju informaciju

**kak eto dolzno rabotat po logike**

kartocka rasshiri a etsia, tekst perenositsia, a dejstviia ostajutsia dostupnymi

**dlia kogo i dlia kakoj celi**

dlia slabovid iasc ix i pozhilyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

polzovatel ne prinimaet zapros slucajno iz-za obrezannogo imeni ili skrytogo konteksta

---

# mnogoiazy cnost

## 178 — perevod zaprosov i bio

**cto eto dolzno delat**

sistema moz et perevodit

* kratkoe soobsenie k zaprosu
* bio
* socialnye celi
* instrukcii pervoj vstrechi
* opisanie gruppy
* priglashenie

**pocemu eto nuzno**

mestnoe soobscestvo moz et byt mnogojazy cnym

**kak eto dolzno rabotat po logike**

original vsegda dostup en, a avtomaticeskij perevod imeet metku

**dlia kogo i dlia kakoj celi**

dlia mezhdunarodnyx i mnogojazy cnyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

jazyk ne stanovitsia absoliutnym barerom dlia bezopasnogo kontakta

---

## 179 — cto ne nado perevodit avtomaticeski

**cto eto dolzno delat**

bez podtverzhdeniia ne nado izmeniat

* username
* imia pitomca
* nazvanie mesta
* adres
* kod vstrechi
* nomer telefona
* nazvanie kliniki
* komandy pitomca
* registracionnye nomera

**pocemu eto nuzno**

oshibka moz et privesti k potere kontakta, ne tom u mestu ili nepon iatnoj komande

**kak eto dolzno rabotat po logike**

takie fragmenty soxraniajutsia v originalnom vide ili imejut rucn uju proverenn uju versiju

**dlia kogo i dlia kakoj celi**

dlia tocn oj koordinacii

**kakoj rezultat dolzen byt dostignut**

perevod ne meniaet username, mesto vstrechi ili slovo, na kotoroe reagiruet pitomec

---

## 180 — stabilnye nazvaniia tipov sviazi

**cto eto dolzno delat**

podpiska, drug, blizkij krug, sovladelec, sitter i drugie statusy dolzny imet odni vnutrennie identifikatory na vseh jazykax

**pocemu eto nuzno**

esli kazdyj perevod sozdaet otdelnyj tip, prava i filtry slomajutsia

**kak eto dolzno rabotat po logike**

v sisteme xranitsia stabiln yj kod, a interfejs pokaz yvaet lokalizovann oe nazvanie

**dlia kogo i dlia kakoj celi**

dlia edinoj mnogoiazy cn oj logiki

**kakoj rezultat dolzen byt dostignut**

druzhba ostajotsia toj ze sviazju nezavisimo ot vybrannogo jazyka interfejsa

---

# analitika i pokazateli kachestva

## 181 — uspeshnost socialnyx zaprosov

**cto eto dolzno delat**

platforma moz et analizirovat agregirovanno

* skolko zaprosov otpravleno
* skolko prin iato
* skolko otkloneno
* skolko iste klo
* skolko privelo k blokirovke
* skolko bylo otpravleno po rekomendacii
* skolko bylo posle sobytija

**pocemu eto nuzno**

eto pomogaet ponimat, polezny li rekomendacii i ne sozdajut li oni spam

**kak eto dolzno rabotat po logike**

analitika ne dolzna raskryvat, kto imenno kogo otklonil, postoronnim polzovateliam ili rekl amod ateliam

**dlia kogo i dlia kakoj celi**

dlia produktovoj komandy i bezopasnosti

**kakoj rezultat dolzen byt dostignut**

sistema ulucshaet relevantnost, ne prevrascha ja lichnye resheniia v publicn yj rejting

---

## 182 — pokazateli kachestva rekomendacij

**cto eto dolzno delat**

mozno izmeriat

* skolko rekomendacij otkryto
* skolko skryto
* skolko privelo k podpis ke
* skolko privelo k druzhbe
* skolko privelo k zhalobe
* skolko povtoriajusc ix profilej
* skolko predlozhenij obiasneny pon iatno

**pocemu eto nuzno**

vysokoe kolichestvo klikov ne oznachaet bezopasn uju ili dolgosrocn uju socialnuju cennost

**kak eto dolzno rabotat po logike**

vazno ucit yvat ne tolko konversiju, no i blokirovki, otmeny i zhaloby

**dlia kogo i dlia kakoj celi**

dlia ulucsheniia algoritma bezopasnogo otkrytiia profilej

**kakoj rezultat dolzen byt dostignut**

rekomendacii stanov iatsia ne tolko privlekatelnymi, no i bolee bezopasnymi i dolgosrocnymi

---

## 183 — pokazateli bezopasnosti

**cto eto dolzno delat**

nuzno analizirovat

* spam zaprosy
* obxod blokirovok
* presledovanie
* fishing
* massovye zhaloby
* falshiv y e profilej
* incidenty posle vstrech
* zaprosy o tocn oj lokacii
* kontakt vzroslyx s nesovershennoletnimi

**pocemu eto nuzno**

socialnaja sistema dolzna ocenivatsia ne tolko po rostu kolichestva sviazej

**kak eto dolzno rabotat po logike**

analitika dolzna byt agregirovannoj, a individualnye incidenty dostupny tolko upolnomochenn oj komande

**dlia kogo i dlia kakoj celi**

dlia bezopasnosti soobscestva

**kakoj rezultat dolzen byt dostignut**

rost socialnogo grafa ne soprovozdaetsia ro stom presledovaniia i moshennicestva

---

## 184 — pokazateli realnoj cennosti

**cto eto dolzno delat**

mozno izmeriat

* dolgosrocnye druzhby
* povtornye sovmestnye progulki
* uchastie v gruppax
* uspeshnye volonterskie sviazi
* podderzhku novickov
* povtornye bezopasnye vstrechi
* socialnye sviazi posle adopcii

**pocemu eto nuzno**

kolichestvo druzej samo po sebe ne pokazyvaet realn uju polzu

**kak eto dolzno rabotat po logike**

metrik i ne dolzny podtalkivat k nakrutke ili sorevnovaniju

**dlia kogo i dlia kakoj celi**

dlia ocenki realnoj socialnoj cennosti produkta

**kakoj rezultat dolzen byt dostignut**

platforma ulucshaet kachestvo sviazej, a ne tolko ix kolichestvo

---

## 185 — ne delat publicn yj rejting druzheliubnosti

**cto eto dolzno delat**

platforma ne dolzna sozda vat odnu publicnuju cifru

* druzheliubnost 95 procentov
* bezopasnost 4,8
* sovmestim so vsemi
* problemn yj pitomec

**pocemu eto nuzno**

takie cifry uproshchajut slozhnoe povedenie, sozdaijut kley ma i mogut byt osnovany na slucajnyx vstrechax

**kak eto dolzno rabotat po logike**

luchshe pokaz yvat konkretn y e razreshennye nabludenija i kontekst

**dlia kogo i dlia kakoj celi**

dlia spravedlivogo otnosheniia k pitomcam i xoziaevam

**kakoj rezultat dolzen byt dostignut**

odin incident ili otsutstvie opyta ne sozdaet postojann uju publicnuju negativnuju metku

---

# minimalnaja versija dlia pervogo zapuska

## 186 — obyazatelnye tipy sviazej

**cto eto dolzno delat**

pervaja versija dolzna podderzhivat

* podpis ku
* zapros na zakryt uju podpis ku
* druzhbu xoziaev
* druzhbu pitomcev
* blizkij krug
* semejn uju sviaz
* blokirovku
* ogranichenie
* skrytie kontenta

**pocemu eto nuzno**

eto minimaln yj nabor dlia realnoj socialnoj seti s pon iatnymi granicami

**kak eto dolzno rabotat po logike**

kazdyj tip imeet otdeln yj status, prava i audit

**dlia kogo i dlia kakoj celi**

dlia bazovyx socialnyx scenariev vseh polzovatelej

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et sledit, druzhit, ogranichivat i blokirovat bez smeshivaniia raznyx prav

---

## 187 — obyazatelnye funkcii zaprosov

**cto eto dolzno delat**

pervaja versija dolzna vkliucat

* otpravku
* kontekst
* prin iatie
* otklonenie
* otmen u
* srok
* zascitu ot dublikatov
* limit castoty
* blokirovku
* zhalobu
* uvedomlenija

**pocemu eto nuzno**

bez polnogo zivotnogo cikla zaprosy budut nakaplivatsia, dublir ovatsia i ispolzovatsia dlia spama

**kak eto dolzno rabotat po logike**

kazdoe dejstvie dolzno byt idempotentnym i proveryat nastojki poluchatelia

**dlia kogo i dlia kakoj celi**

dlia vseh novyx socialnyx kontaktov

**kakoj rezultat dolzen byt dostignut**

zaprosy rabotajut predskazuemo i ne narushajut lichnye granicy

---

## 188 — obyazatelnye funkcii rekomendacij

**cto eto dolzno delat**

pervaja versija moz et ispolzovat

* gorod
* vid pitomca
* interesy
* obsc ix druzej
* obsc ie gruppy
* socialnye celi
* jazyki

**pocemu eto nuzno**

eto daet bazovuju relevantnost bez glubokogo ispolzovaniia cuvstitelnyx dannyx

**kak eto dolzno rabotat po logike**

kazdaia rekomendacija imeet prichinu, knopku skryt i nastojku ne rekomendovat profil

**dlia kogo i dlia kakoj celi**

dlia novyx i aktivnyx polzovatelej

**kakoj rezultat dolzen byt dostignut**

polzovatel polucaet pon iatnye predlozheniia i moz et upravliat algoritmom

---

## 189 — obyazatelnye funkcii bezopasnosti

**cto eto dolzno delat**

pervaja versija dolzna vkliucat

* blokirovku vsego akkaunta
* zascitu ot povtornyx zaprosov
* zaprosy na soobsenija
* limit novyx kontaktov
* zhaloby
* anti fishing preduprezhdeniia
* skrytie tocn oj lokacii
* usilennye pravila dlia nesovershennoletnix
* audit
* apellaciju

**pocemu eto nuzno**

socialnye funkcii bez etix mehanizmov sozdaijut bolsh oj risk presledovaniia i moshennicestva

**kak eto dolzno rabotat po logike**

zascita dolzna primeniatsia na servere i ko vsem upravliaemym profilem problemnogo akkaunta pri vybrannom masshtabe

**dlia kogo i dlia kakoj celi**

dlia vsego soobscestva

**kakoj rezultat dolzen byt dostignut**

polzovatel moz et bystro prekratit kontakt i soobscit o sistemnom narushenii

---

## 190 — cto mozno dobavit pozze

**cto eto dolzno delat**

posle stabilizacii bazovoj sistemy mozno dobavit

* rasshirenn uju sovmestimost pitomcev
* ai rekomendacii s objasneniem
* avtomaticeskoe predlozhenie parallel n yx progulok
* vremen n uju geolokaciju
* maskirovannye zvonki
* mnogoetapn oe znakomstvo
* professionaln yj razbor sovmestimosti
* semanticeskij poisk
* rekomendacii po rutin e
* mestnye socialnye krugi
* integraciju s kalendarem
* bezopasnostn yj check-in
* rasshirenn uju anti stalking sistemu
* detskie semejnye profilej
* mezhplatformenn yj socialn yj graf
* proverennye lokalnye kluby

**pocemu eto nuzno**

eti funkcii polezny, no oni dolzny opiratsia na stabilnye bazovye sviazi, blokirovki i prava

**kak eto dolzno rabotat po logike**

kazdaia novaia funkcia podkliucaetsia k odnoj sisteme socialnyx sviazej, a ne sozdaet alternativn y e spiski kontaktov

**dlia kogo i dlia kakoj celi**

dlia dalnejshego razvitiia socialnogo opyta

**kakoj rezultat dolzen byt dostignut**

platforma rasshiri a etsia bez putanicy, dublikatov i obxoda bazovyx pravil privatnosti

---

# idealnye scenarii

## 191 — idealnyj scenarij podpiski na pitomca

andrej naxodit publicn yj profil popugaja keshi i xocet sledit za ego fotografijami

on podpis yvaetsia na keshu, no ne dobavliaet ego xoziajina v druzia

v rezultate andrej vidit publicnye publikacii keshi, no ne vidit lichnye posty xoziajina, ego telefon, lokaciju i zakrytye semejnye albomy

---

## 192 — idealnyj scenarij druzhby xoziaev

andrej i marius neskolko raz vstrechalis na gruppovyx progulkax

marius otpravliaet zapros na druzhbu s primechaniem, cto oni byli na sobytii v parke

andrej vidit obsc uju grup pu, sobytie i bazovyj profil mariusa

on prinimaet zapros, no ne dobavliaet ego v blizkij krug

v rezultate oni mogut pisat drug drugu i videt kontent dlia oby cnyx druzej, no zakrytye semejnye istorii ostajutsia nedostupnymi

---

## 193 — idealnyj scenarij druzhby pitomcev

baks i sobaka mariusa reguliarno gul iajut parallel n o

andrej otpravliaet zapros druzhby mezhdu profiljami pitomcev i vy biraet tip parallel n y e progulki

marius podtverzhdaet

profilej pitomcev stanov iatsia druzjami, no sistema ne pishet, cto oni idealno sovmestimy ili mogut bezopasno igrat bez povodka

---

## 194 — idealnyj scenarij novogo polzovatelia

novyj xoziajin v vilniuse poka ne znaet nikogo

on ukazyvaet

* spokojny e progulki
* sobaka srednego razmera
* predpoctitelnyj russkij ili litovskij jazyk
* tolko publicnye mesta
* ne xocet bolsh ix grupp

sistema rekomenduet neskolko profilej s obiasneniem

* v vashem gorode
* ishchut spokojny e progulki
* gov oriat na odnom iz vybrannyx jazykov
* sosto iat v toj ze mestnoj gruppe

---

## 195 — idealnyj scenarij bez tocn oj lokacii

andrej ishchet xoziaev v svoem rajone

sistema pokaz yvaet profilej iz priblizitelnoj zony, no ne pishet, cto odin celovek zhivet v 180 metrax

pered pervoj progulkoj storony vybirajut publicn yj park, a domashnie adres a ostajutsia skrytymi

---

## 196 — idealnyj scenarij pervoj vstrechi

baks i novyj pitomec esio ne vstrechalis

xoziajie vidat, cto oboim pitomcam nuzna distancija i oni predpochitajut spokojny e progulki

oni plan irujut dvadcatiminutn uju parallel n uju progulku v publicnom parke

v priglashenii ukazano

* ne podvodit pitomcev srazu blizko
* ne davat cuzie lakomstva
* ne otpusk at s povodka
* mozno zaversit vstrechu ranshe

---

## 197 — idealnyj scenarij otklonennogo zaprosa

neznakom yj polzovatel bez obsc ix grupp otpravliaet andreju zapros bez konteksta

andrej otkloniaet ego i vy biraet ne prinimat povtorn y e zaprosy ot etogo akkaunta

otpravitel ne polucaet lichn oj prichiny i ne moz et otpravit novyj zapros na sledujusc ij den

---

## 198 — idealnyj scenarij navi azchivogo kontakta

polzovatel neskolko raz pishet andreju cerez raznye profilej pitomcev

andrej vy biraet zablokirovat akkaunt i vse upravliaemye im profilej

sistema

* zakryvaet chat y
* udal iaet aktivnye zaprosy
* skryvaet profilej iz rekomendacij
* blokiruet upominanija
* ne pokaz yvaet status v seti
* soxraniaet dokazatelstva zhaloby

---

## 199 — idealnyj scenarij blizkogo kruga

andrej dobavliaet dvux blizkix druzej v otdeln yj krug

oni vidat zakrytye istorii baksa i polucajut srocn oe uvedomlenie, esli baks poterialsia

oni ne polucajut avtomaticeski medkartocku, gps istoriju ili pravo redaktirovat profil

---

## 200 — idealnyj scenarij sittera

sitter polucaet vrem enn yj kontakt na vyxodnye

on moz et

* pisat andreju
* videt kartocku baksa
* polucat vrem enn yj gps tolko vo vremia progulki
* otpravliat otchet

posle zaversheniia uslugi vrem ennyj kontakt istekaet, a sitter ne stanovitsia avtomaticeski drugom ili podpiscikom

---

## 201 — idealnyj scenarij spama

novyj akkaunt za desiat minut otpravliaet sto odinakov yx zaprosov s reklamnoj ssylkoj

sistema

* ogranichivaet novye zaprosy
* blokiruet ssylku
* zaprashivaet podtverzhdenie
* otpravliaet risk profil na proverku
* ne dostavliaet ostalnye zaprosy
* preduprezhdaet uzhe poluchivsh ix soobsenie pri podtverzhdennom moshennicestve

---

## 202 — idealnyj scenarij zakrytogo profilia

xoziajin luny razreshaet podpis ku tolko posle odobreniia

novyj polzovatel otpravliaet zapros na podpis ku, no ne zapros na druzhbu

xoziajin prinimaet ego kak podpiscika

on vidit publikacii dlia podpiscikov, no ne moz et pisat napriamu, videt spisok druzej ili priglashat lunu na lichn uju vstrechu bez otdelnogo razresheniia

---

## 203 — idealnyj scenarij priuta

polzovatel podpis yvaetsia na priut, no vy biraet tolko kategorii

* novye pitomcy dlia adopcii
* srocn y e potrebnosti
* volonterskie sobytija

on ne polucaet kazdyj oby cn yj post, reklamu i vse fotografii iz ezhednevn oj raboty

---

## 204 — idealnyj scenarij nesovershennoletnego

podrostok upravliaet socialnym profilem semejnogo pitomca pod kontrolem vzroslogo

neznakom yj vzrosl yj ne moz et otpravit emu videozvonok ili priam oe priglashenie na lichn uju vstrechu

zapros na gruppovuju progulku napravliaetsia semejnomu upravliajuscemu

tocn aja lokacija i vozrast podrostka ne pokaz yvajutsia

---

## 205 — idealnyj scenarij obxoda blokirovki

zablokirovann yj celovek sozdaet novyj profil pitomca i otpravliaet pocti takoe ze soobsenie

sistema zam ecaet sviazannye signaly i ne dostavliaet zapros srazu

andrej polucaet vozmoznost dobavit etot slucaj k uze sushestvujusc ej zhalobe na presledovanie

moderator vidit obsch uju istoriju, a ne odin izolirovann yj profil

---

## 206 — idealnyj scenarij incidenta na progulke

na gruppovoj progulke dva pitomca konfliktujut

vmesto publicnoj travli xoziajie sozdaijut strukturirovann yj incident

* cto proizoshlo
* v kakom kontekste
* byly li travmy
* kto videl
* cto sdelali
* nuzhna li klinika

organizator vrem enno ogranichivaet novye vstrechi etoj pary, no ni odin pitomec ne polucaet publicn uju bessrocn uju metku opasnyj

---

## 207 — idealnyj scenarij peredaci pitomca

luna peredaetsia novomu xoziajinu posle adopcii

pri peredace sistema predlagaet

* soxranit druzej pitomca
* priostanovit novye zaprosy
* skryt star y e lichnye dialogi priuta
* soxranit publicnye albomy
* ubrat dostup predyduscej perederzki
* obnovit socialn uju lokaciju

novyj xoziajin reshaet, kakie socialnye sviazi prodolzhat

---

## 208 — idealnyj scenarij memorialnogo profilia

posle perevoda profilia baksa v memorialnyj rezhim

* novye zaprosy druzhby otkliucajutsia
* rekomendacii progulok ostanavlivajutsia
* st ar y e druzia ostajutsia v istorii
* blizkij krug moz et ostavliat memorialnye soobsenija
* profil ne pokaz yvaetsia kak aktivn yj pitomec, ishchusc ij druzej

---

# itogovyj rezultat punkta 3

posle polnoj realizacii punkta 3 socialnaja set dolzna poluchit ne prosto knopki podpisatsia i dobavit v druzia, a polnocenn uju sistemu socialnyx otnoshenij s pon iatnymi tipami, pravami, granicami i bezopasnostju

v rezultate polzovatel dolzen moc

* podpisatsia na pitomca, xoziajina, specialista ili priut
* sozdat dvustoronnju druzhbu xoziaev
* sozdat otdeln uju druzhbu mezhdu pitomcami
* razlikat druzej, podpiscikov, sovladelcev, sitterov i professionalnye kontakty
* sozdat blizkij krug
* naznacit ekstrennogo kontakta
* otpravit kontekstn yj zapros
* prin iat zapros s ogranichenn oj vidimostiu
* tixo otklonit ili otmenit zapros
* zascititsia ot povtornyx i massovyx obrashenij
* najti profilej po interesam, jazyku, vidu i obobschonn oj lokacii
* ponimat, pocemu profil rekomendovan
* otkliucit personalizaciju i rekomendacii
* plan iro vat bezopasn uju perv uju vstrechu
* delitsia vrem enn oj lokaciej tolko s konkretnymi uchastnikami
* organizovyvat parallel n y e i gruppovye progulki
* zaversit druzhbu bez obyazatelnoj blokirovki
* ogranichit, skryt ili zablokirovat problemn yj profil
* zablokirovat ves akkaunt so vsemi upravliaemymi profilej
* soobscit o presledovanii, moshennicestve ili obxode blokirovki
* zascitit nesovershennoletnix ot neznakom yx vzroslyx i lichnyx vstrech
* skryt spisok druzej, podpiscikov i socialnyx sviazej
* soxranit socialn uju istoriju pri peredace pitomca
* pravilno obrabotat deaktivaciju, udaleniie i memorialn yj rezhim
* polzovatsia vsemi funkcijami na telefone, kompiutere, s klaviaturoj i ekrannym diktorom

glavn oe dostizenie etogo punkta zakliucaetsia v tom, cto socialnye sviazi budut sozda vat realn uju cennost dlia xoziaev i pitomcev, no ne budut avtomaticeski otkryvat lichnye dannye, medkartocku, gps, adres, semejn y e otnosheniia ili administrativnye prava

sledujusc ij punkt — lenta publikacij, posty, fotografii, video, istorii, reakcii, kommentarii, upominanija, xeshtegi, reposty i polnaja logika rasprostraneniia kontenta
</social-relationships-source-revision>
