# Installatie — PiratenKrakers WordPress-thema

## 1. Uploaden

Gebruik het bestand:

`/home/rick/piratenkrakers/piratenkrakers.zip`

WordPress → **Weergave → Thema’s → Nieuwe toevoegen → Thema uploaden** → kies de zip → **Nu activeren**.

Of via SFTP: map `piratenkrakers/` naar `wp-content/themes/piratenkrakers/`.

## 2. Permalinks

Instellingen → Permalinks → **Wijzigingen opslaan** (flush). Nodig voor `/djs/` en `/programma/`.

## 3. Radio koppelen

**PiratenKrakers → Radio**

1. Plak je echte **stream-URL** (niet de website-URL).
2. Kies de juiste **metadata-adapter**.
3. Plak je echte **metadata-URL**.
4. Opslaan.

Zonder deze twee URL’s toont de site de offline-staat en fallback-artwork. Dat is bewust.

## 4. Menu & pagina’s

Bij eerste activatie maakt het thema Home, Live, Verzoekjes, Contact, Nieuws, DJ’s en het weekprogramma aan.

## 5. Wat jij nog moet leveren

- Icecast / AzuraCast / SHOUTcast stream-URL
- Now-playing API of status-json
- Echte social-links
- DJ-foto’s (uitgelichte afbeelding per DJ)
- Eventueel eigen logo (vervangt het meegeleverde merkteken)

Uitgebreide uitleg: `piratenkrakers/README.md` en `piratenkrakers/BRAND.md`.
