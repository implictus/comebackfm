# PiratenKrakers — WordPress-thema

Officieel platformthema voor **PiratenKrakers.nl**. Geen generiek radiotemplate: eigen merk, eigen radio-engine, eigen admin.

Slogan: **Muziek uit het hart**

## Installeren

1. Zip het mapje `piratenkrakers` (niet de buitenste projectmap).
2. WordPress → Weergave → Thema’s → Nieuwe toevoegen → Thema uploaden.
3. Activeer **PiratenKrakers**.
4. Bij activatie worden demo-pagina’s, menu, DJ’s, programma’s en nieuws aangemaakt (eenmalig).

Overslaan van demo-content: zet in `wp-config.php`:

```php
define( 'PK_SKIP_DEMO', true );
```

## Configureren (verplicht voor échte radio)

WordPress-admin → **PiratenKrakers → Radio**

Vul **jouw** infrastructuur in. Er staan **geen nep-API’s** in het thema.

### Stream 1 — PiratenKrakers Main

| Veld | Wat je invult |
|---|---|
| Stream-URL | Directe audio-URL, bijv. `https://JOUW-SERVER/listen/piratenkrakers/radio.mp3` |
| Formaat | mp3 / aac / ogg / opus |
| Metadata-adapter | `custom` / `azuracast` / `icecast` / `shoutcast` |
| Metadata-URL | Zie hieronder |
| Artwork-URL | Optioneel apart cover-endpoint |

**AzuraCast:** adapter `azuracast`, metadata-URL  
`https://JOUW-AZURACAST/api/nowplaying/station_shortcode`

**Icecast:** adapter `icecast`, metadata-URL  
`https://JOUW-ICECAST/status-json.xsl`  
Mount: pad van de mount, bijv. `/radio.mp3`

**SHOUTcast v2:** adapter `shoutcast`, metadata-URL  
`http://HOST:POORT/stats?sid=1&json=1`

**Custom JSON:** adapter `custom`, metadata-URL naar jouw JSON. Mapping met dot-paths, standaard:

```
artist, title, artwork, dj, show, listeners, is_live
```

Voorbeeld eigen JSON:

```json
{
  "artist": "Frans Bauer",
  "title": "Heb je even voor mij",
  "artwork": "https://…/cover.jpg",
  "dj": "DJ Nachtvlucht",
  "show": "De Avondploeg",
  "listeners": 142,
  "is_live": true
}
```

### Stream 2 — PiratenKrakers Alternatief

Zelfde velden, andere URL. Zet “Actief” aan als de tweede stream live is.

### Branding

**PiratenKrakers → Instellingen:** logo, favicon, kleuren, Facebook / Instagram / TikTok / YouTube, contact.

## Architectuur

```
Frontend (thema)  →  GET /wp-json/pk/v1/now-playing
WordPress CMS     →  DJ's, programma's, nieuws, verzoekjes
Radio-engine      →  adapters (Icecast / SHOUTcast / AzuraCast / SAM / custom JSON)
Externe stream    →  audio-URL in Radio-instellingen
```

Zonder stream-URL is `demo: true`: de UI gebruikt de programmagids + branded fallback. Geen hardcoded DJ-namen in templates.

```
GET  /wp-json/pk/v1/now-playing
GET  /wp-json/pk/v1/now-playing/{stream}
GET  /wp-json/pk/v1/streams
GET  /wp-json/pk/v1/status
GET  /wp-json/pk/v1/schedule
GET  /wp-json/pk/v1/djs
POST /wp-json/pk/v1/request
```

## Wat de frontend praat

De speler praat **alleen** met WordPress, nooit rechtstreeks met Icecast/AzuraCast.

```
GET  /wp-json/pk/v1/now-playing
GET  /wp-json/pk/v1/now-playing/{stream}
GET  /wp-json/pk/v1/streams
GET  /wp-json/pk/v1/status
POST /wp-json/pk/v1/request
```

Genormaliseerd payload:

```json
{
  "station": "PiratenKrakers.nl",
  "stream_id": "main",
  "is_live": true,
  "offline": false,
  "artist": "…",
  "title": "…",
  "artwork": "…",
  "dj": "…",
  "show": "…",
  "listeners": 0,
  "source": "azuracast",
  "demo": false,
  "show_start": "18:00",
  "show_end": "22:00"
}
```

Ontbreekt metadata, dan: stationslogo, “PiratenKrakers.nl”, “Live radio”.  
Is de stream offline: **“De uitzending is momenteel offline.”**  
DJ/programma worden aangevuld uit de WordPress-programmagids als de API ze niet stuurt.

## Radio-engine vs. thema

| Laag | Bestanden | Vervangen zonder restyle |
|---|---|---|
| Server-engine | `inc/radio/*` | ja |
| REST-contract | `inc/rest.php` | stabiel houden |
| JS-engine | `assets/js/radio-engine.js` | ja |
| Player-UI | `assets/js/player.js` + `template-parts/player-sticky.php` | nee (uiterlijk) |
| CSS | `assets/css/main.css` | nee (uiterlijk) |

Nieuwe metadata-bron: registreer een adapter via filter `pk_radio_adapters`.

## Navigatie zonder stream-herstart

`assets/js/theme.js` onderschept interne links (PJAX) en verwisselt alleen `#pk-app`. Header, footer en de sticky player blijven staan, de audio-element ook. Fallback: gewone paginaload.

## Beheer

- **DJ’s** — CPT `pk_dj`
- **Programma’s** — CPT `pk_show` (dagen, start/eind, DJ, genre)
- **Verzoekjes** — CPT `pk_request` (niet publiek; honeypot + nonce + rate-limit)
- **Nieuws** — standaard berichten

## PWA / later

- Web app manifest via `/?pk_manifest=1`
- Iconen 192/512 aanwezig
- Architectuur klaar voor push, chat, shoutbox, stats, meerdere streams (stream 2 zit al in de settings)

## Eisen

- WordPress 6.4+
- PHP 8.1+
- HTTPS aangeraden (autoplay/stream)

## Licenties fonts

Outfit en Caveat: SIL Open Font License. Self-hosted, geen Google-request.
