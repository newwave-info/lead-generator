# Web Analysis — Workflow & Data Contract

Il workflow **Lead Generator | Web Development** esegue un’analisi qualitativa e strategica del sito web aziendale, produce un report revisionato e lo salva su WordPress come post di tipo `analisi`. Il focus è su posizionamento, messaggi chiave, branding, trust, funnel di conversione, contenuti e opportunità per agenzie di comunicazione e marketing B2B.

## Descrizione Generale

- Analisi automatica del dominio aziendale collegato alla scheda.
- Output strutturati per strategia, revisione consulenziale e sintesi dati.
- Pubblicazione o aggiornamento dell’analisi su WordPress, collegandola all’azienda madre.

## Scopo

Produrre in modo continuativo:

- Analisi strategica completa (testo “Perplexity deep research”).
- Revisione consulenziale senior (“Analisi migliorata”).
- Sintesi strutturata in JSON con insight e metriche chiave.
- Upsert del post `analisi` collegato all’azienda, con tracciamento dello stato.

## Workflow Step-by-Step

### 1. Trigger — Webhook

- **Tipo:** Webhook (`POST`)
- **Percorso:** `/web-development-agent`
- **Input obbligatorio:** `company_id` (ID WordPress dell’azienda)
- Attiva l’intera pipeline di analisi a partire dai dati aziendali correnti.

### 2. Read Azienda WP

- **Nodo:** `Read Azienda WP`
- **Endpoint:** `https://lead.perspect.it/wp-json/wp/v2/azienda/{{ company_id }}`
- **Autenticazione:** `Lead 18kPRo WP` (HTTP Basic Auth)
- Recupera il payload ACF completo dell’azienda (ragione sociale, dominio, settore, descrizione, ecc.) da passare agli agenti successivi.

### 3. Agente di analisi

- **Modello:** GPT-5 Search API
- **Ruolo:** consulente strategico senior (brand, comunicazione, UX)
- Genera un testo narrativo in italiano rivolto a CEO/CMO, organizzato in sezioni:
  - Sintesi esecutiva
  - Brand e posizionamento
  - Credibilità e trust
  - Funnel & conversione
  - Contenuti
  - SEO
  - UX & UI
  - Rischi e red flag
  - Opportunità e raccomandazioni
  - Domande intelligenti al cliente

### 4. Agente di revisione

- **Modello:** GPT-5
- **Ruolo:** revisore strategico senior
- Migliora il testo generato eliminando ridondanze, rafforzando tono e chiarezza, aggiungendo insight e garantendo coerenza narrativa.
- Output finale: **“Analisi migliorata”** con chiusura obbligatoria che include:
  - Domande critiche da porre al prospect (max 6)
  - Idee di valore da proporre come agenzia (max 5)

### 5. Agente di sintesi

- **Modello:** GPT-5 Mini
- **Ruolo:** analista senior di estrazione strutturata
- Riceve l’analisi revisionata e restituisce **solo** un JSON con schema:

```json
{
  "riassunto": "string (≤280 caratteri)",
  "messaggi_principali": ["..."],
  "promessa_valore": "string",
  "tono_di_voce": "string",
  "differenziazione": ["..."],
  "coerenza_comunicativa": "string",
  "punti_forza": ["..."],
  "punti_debolezza": ["..."],
  "opportunita": ["..."],
  "azioni_rapide": ["..."],
  "target_commerciali": ["..."],
  "potenziale": "string",
  "idee_valore_agenzia": ["..."],
  "domande_prospect": ["..."],
  "rischi_mitigazioni": [
    { "rischio": "string", "mitigazione": "string" }
  ],
  "priorita_temporali": {
    "entro_30_giorni": ["..."],
    "entro_90_giorni": ["..."],
    "entro_12_mesi": ["..."]
  },
  "prove_trust": {
    "team_visibile": true,
    "case_study": false,
    "testimonianze": false,
    "certificazioni": ["..."]
  },
  "fonti": [
    { "titolo": "string", "url": "https://..." }
  ],
  "voto_qualita_analisi": 0,
  "voto_qualita_dati": 0,
  "attendibilita_dati": {
    "alert": false,
    "note": "string"
  }
}
```

- Regole:
  - Liste da 3 a 8 voci, ordinate per impatto e senza duplicati.
  - Azioni rapide operative, completabili in ≤2 settimane.
  - Le priorità temporali sono ripartite per bucket (30/90/12 mesi).
  - I voti valutano qualità dell’analisi e dei dati di partenza (scala 0–100).

### 6. Parsing — Code Node

- Valida il JSON prodotto dall’agente di sintesi.
- Normalizza i campi testuali, deduplica le liste e rende esplicite le strutture nidificate (priorità temporali, prove trust, rischi).
- Calcola i conteggi (`count_*`) per ogni array da salvare come numero ACF (`numero_*`).
- Converte i voti in interi clampati 0–100.
- Serializza i blocchi strutturati (trust, fonti, rischi) prima dell’upsert e allega `output_agente_raw` per audit.

### 7. Upsert WordPress

- **Nodo:** `Upsert WP`
- **Metodo:** `POST` su `https://lead.perspect.it/wp-json/wp/v2/analisi` (aggiornamento tramite ID quando esiste).
- **Campi principali scritti:**
  - `title`: `{{ company_name }} | Web Analysis`
  - `status`: `publish`
  - `agent_type`: `7` (Web Development)
  - **ACF aggiornati:**
    - Campi narrativi: `analisy_perplexity_deep_research`, `revisione_analisi_completa`, `riassunto`
    - SWOT & azioni: `punti_di_forza`, `punti_di_debolezza`, `opportunita`, `azioni_rapide` + rispettivi `numero_*`
    - Sintesi brand: `messaggi_principali`, `promessa_di_valore`, `tono_di_voce`, `coerenza_comunicativa`, `elementi_differenzianti` + `numero_*`
    - Target & value: `target_commerciali`, `idee_di_valore_perspect`, `domande_prospect`, `priorita_temporali` + `numero_*`
    - Telemetria: `voto_qualita_analisi`, `qualita_dati`
    - Logging analisi: `analysis_last_status_code`, `analysis_last_message`, `analysis_last_at`
    - Aggancio: `parent_company_id`

### 8. Logging

- **Compose Upsert Result:** calcola `upsert_ok`, `upsert_status_code`, `upsert_message`, `analysis_id`, `analysis_link`, `upsert_at_iso`.
- **Log Upsert Result WP:** `POST https://lead.perspect.it/wp-json/wp/v2/azienda/{company_id}` aggiornando:
  - `analysis_last_status_code`
  - `analysis_last_message`
  - `analysis_last_at`

### 9. Webhook Response

- **Nodo:** `Return Status`
- Risposta JSON al chiamante:

```json
{
  "ok": true,
  "status": 200,
  "message": "OK"
}
```

- `Response Code`: `{{ upsert_status_code || 200 }}`

## Mappatura Campi ACF (Post type: analisi)

| Campo ACF | Tipo | Provenienza Parsing | Note |
| --- | --- | --- | --- |
| `parent_company_id` | post_object | ID azienda | Collegamento WP |
| `riassunto` | text | `riassunto` | ≤280 caratteri |
| `analisy_perplexity_deep_research` | textarea | Agente analisi base | Testo completo AI (prima stesura) |
| `revisione_analisi_completa` | textarea | Agente revisione | Versione consulenziale |
| `messaggi_principali` | textarea | array → newline | Key messaging |
| `numero_messaggi_principali` | number | `count_messaggi_principali` | Conteggio dedup |
| `promessa_di_valore` | textarea | `promessa_valore` | Unique selling proposition |
| `tono_di_voce` | textarea | `tono_di_voce` | |
| `elementi_differenzianti` | textarea | `differenziazione` | USP puntuali |
| `numero_elementi_differenzianti` | number | `count_differenziazione` | |
| `coerenza_comunicativa` | textarea | `coerenza_comunicativa` | Valutazione narrativa |
| `punti_di_forza` | textarea | `punti_forza` | Lista dedup |
| `numero_punti_di_forza` | number | `count_punti_forza` | |
| `punti_di_debolezza` | textarea | `punti_debolezza` | |
| `numero_punti_di_debolezza` | number | `count_punti_debolezza` | |
| `opportunita` | textarea | `opportunita` | |
| `numero_opportunita` | number | `count_opportunita` | |
| `azioni_rapide` | textarea | `azioni_rapide` | Task ≤2 settimane |
| `numero_azioni_rapide` | number | `count_azioni_rapide` | |
| `target_commerciali` | textarea | `target_commerciali` | Segmenti priorità |
| `numero_target_commerciali` | number | `count_target_commerciali` | |
| `idee_di_valore_perspect` | textarea | `idee_valore_agenzia` | Proposte consulenziali |
| `numero_idee_di_valore` | number | `count_idee_valore_agenzia` | |
| `domande_prospect` | textarea | `domande_prospect` | Domande per discovery |
| `numero_domande` | number | `count_domande_prospect` | |
| `rischi` | textarea | `rischi_mitigazioni` serializzato | Elenco rischi (mitigazioni opzionali) |
| `numero_rischi` | number | `count_rischi_mitigazioni` | |
| `priorita_temporali` | textarea | `priorita_temporali` serializzato | Bucket 30/90/12 mesi |
| `voto_qualita_analisi` | number | `voto_qualita_analisi` | 0–100 |
| `qualita_dati` | number | `voto_qualita_dati` | 0–100 |
| `analysis_last_status_code` | number | Upsert WP result | HTTP/business esito |
| `analysis_last_message` | textarea | Upsert WP result | Messaggio log |
| `analysis_last_at` | text | Upsert WP result | Timestamp ISO |

> **Nota sui formati**: gli array vengono inviati come stringhe JSON (`["voce 1","voce 2"]`) perché i campi ACF sono `textarea`. Il front-end converte e formatta in liste leggibili.

## Mappatura Campi ACF (Post type: analisi)

### Collegamento e testi base

- `parent_company_id` — `post_object`; associa il post all’azienda sorgente.
- `analisy_perplexity_deep_research` — `wysiwyg`; analisi iniziale generata dall’agente.
- `revisione_analisi_completa` — `wysiwyg`; versione consulenziale revisionata.
- `riassunto` — `text`; executive summary ≤280 caratteri.

### Brand, messaggi e posizionamento

- `messaggi_principali` — `textarea`; lista (serializzata JSON) dei claim chiave.
- `numero_messaggi_principali` — `number`; conteggio dedup dei messaggi.
- `promessa_di_valore` — `textarea`; value proposition sintetica.
- `tono_di_voce` — `textarea`; indicazioni di stile comunicativo.
- `elementi_differenzianti` — `textarea`; punti di differenziazione competitiva.
- `numero_elementi_differenzianti` — `number`; conteggio relativo.
- `coerenza_comunicativa` — `textarea`; valutazione qualitativa della coerenza narrativa.

### SWOT & azioni

- `punti_di_forza` / `punti_di_debolezza` / `opportunita` / `azioni_rapide` — `textarea`; liste normalizzate (serializzate JSON).
- `numero_punti_di_forza` / `numero_punti_di_debolezza` / `numero_opportunita` / `numero_azioni_rapide` — `number`; conteggi calcolati dal parsing.

### Target, valore e discovery

- `target_commerciali` — `textarea`; segmenti prioritari individuati.
- `numero_target_commerciali` — `number`; quantità segmenti deduplicati.
- `idee_di_valore_perspect` — `textarea`; proposte consulenziali ad alto valore.
- `numero_idee_di_valore` — `number`; conteggio idee.
- `domande_prospect` — `textarea`; domande per la fase di discovery.
- `numero_domande` — `number`; total domande critiche.
- `rischi` — `textarea`; elenco (JSON) di rischi con eventuali mitigazioni.
- `numero_rischi` — `number`; conteggio elementi rischi/mitigazioni.
- `priorita_temporali` — `textarea`; struttura JSON con bucket `entro_30_giorni`, `entro_90_giorni`, `entro_12_mesi`.

### Telemetria e quality score

- `voto_qualita_analisi` — `number`; valutazione 0–100 sulla qualità dell’output.
- `qualita_dati` — `number`; attendibilità percepita del dato di partenza (0–100).
- `analysis_last_status_code` — `number`; esito HTTP/business dell’ultimo upsert.
- `analysis_last_message` — `textarea`; messaggio log dell’ultimo run.
- `analysis_last_at` — `text`; timestamp ISO 8601 dell’ultimo aggiornamento salvato.

## Flusso Logico

`Webhook → Lettura azienda → Agente di analisi → Agente di revisione → Agente di sintesi → Parsing → Upsert WP → Logging → Return Status`

## Output WordPress

- **Post type:** `analisi`
- **Tassonomia:** `agent_type = 7 (Web Development)`
- **Slug:** auto-generato (`{azienda}-web-analysis`)
- **Relazione:** `acf[parent_company_id]` collega all’azienda analizzata
- **Status:** `publish`

## Note Operative

- Tutti i modelli OpenAI utilizzano la credenziale `OpenAi account`.
- Timeout HTTP globale: 30 secondi.
- Le strutture JSON (`rischi_mitigazioni`, `priorita_temporali`, `prove_trust`, `fonti`) devono mantenere le chiavi documentate; il parsing serializza automaticamente in stringa prima dell’upsert.
- I backup degli output (analisi iniziale e revisione) restano disponibili per audit e per eventuale riutilizzo in altri moduli (Company Enrichment, Lead Qualification).
