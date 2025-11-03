# EAA Accessibility Analysis — Workflow & Data Contract

Questo documento descrive il workflow **Lead Generator | EAA Agent** e il relativo data contract tra n8n, gli agenti AI e WordPress. L’obiettivo è produrre un audit sulla conformità EAA/WCAG 2.1 Level AA per i siti delle aziende (`post_type` `azienda`) e salvare l’output strutturato come post `analisi`.

## Obiettivo & Contesto

- Evidenziare in modo oggettivo il livello di conformità all’European Accessibility Act, combinando audit automatici (WAVE + Lighthouse) con ragionamento consulenziale.
- Quantificare effort di remediation in **ore/uomo** e stimare il rischio legale/commerciale per settore.
- Produrre un report riutilizzabile dal team sales/consulting e aggiornare la tassonomia `agent_type` (`eaa-accessibility`).

Asset di riferimento:

- ACF export: `docs/json/acf/acf-export-2025-11-03.json` (Field Group `Analisi`).
- Workflow n8n: `docs/json/n8n/Lead Generator _ EAA Agent.json`.

## Workflow n8n — Panoramica Step

1. **Trigger** (`POST /webhook/eaa-accessibility-agent`): atteso `company_id` nel body JSON.
2. **Read Azienda WP** (HTTP Request)  
   - Endpoint: `https://lead.perspect.it/wp-json/wp/v2/azienda/{company_id}`  
   - Recupera dati ACF dell’azienda (domain, settore, ecc.) per contestualizzare gli agenti.
3. **Wave API**  
   - Endpoint: `https://wave.webaim.org/api/request`  
   - Query: `key=S151MWAV5944`, `url={{ domain }}`, `format=json`, `reporttype=4`.  
   - Restituisce statistiche errori/contrasti/alert e link WAVE AIM.
4. **Microlink**  
   - Endpoint: `https://api.microlink.io`  
   - Query: `url={{ domain }}`, `insights.lighthouse=true`, `insights.lighthouse.onlyCategories[]=accessibility`.  
   - Fornisce Lighthouse accessibility score e audit dettagliati.
5. **Merge** (`Merge` + `Code`)  
   - Unisce Wave + Microlink.  
   - Calcola punteggi combinati e clusterizza violazioni:
     - `wave_errors`, `wave_contrast`, `wave_alerts`, `lighthouse_critical`, `lighthouse_warnings`.
     - Quick wins vs major issues e lista `eaa_blockers`.  
   - Punteggio combinato: `score = wave_score*0.7 + lighthouse_score*0.3`.  
     - `wave_score = max(0, 100 - min(100, (errors + contrast + alert*0.5)*3))`.  
     - Classificazione `eaa_compliance`: `conforme_AA`, `parziale`, `non_conforme`.
   - Salva metadata: link report Wave/Lighthouse, `audit_timestamp`, `has_dual_audit`.
6. **Agente di analisi** (`gpt-5-search-api`)  
   - Prompt consulenziale senior accessibilità.  
   - Output: report narrativo (Sintesi Esecutiva + POUR + Deep Dive + Gap manuali + Roadmap ore/uomo + ROI).  
7. **Agente di revisione** (`gpt-5`)  
   - Ottimizza testo (tono C-suite/legal, aggiunge dati mancanti, forza uso ore/uomo, include fonti).  
8. **Agente di sintesi** (`gpt-5`)  
   - Produce **SOLO JSON** secondo schema definito (vedi sotto).  
   - Riceve come contesto: dati azienda, violazioni, punteggi, best practice EAA, note legali italiane 2025.
9. **Parsing** (Code)  
   - Valida il JSON, normalizza stringhe/array, calcola conteggi, clamp 0–100 per i voti.  
   - Aggiunge metadati (`lunghezza_report_discorsivo`, `totale_elementi`, `output_agente_raw`).
10. **Upsert WP** (HTTP Request)  
    - Se esiste analisi collegata all’azienda → `POST https://lead.perspect.it/wp-json/wp/v2/analisi/{ID}`.  
    - Altrimenti crea nuovo post su `wp-json/wp/v2/analisi`.  
    - Titolo: `{{ Ragione sociale }} - Analisi EAA Accessibility`.  
    - `status=publish`, `agent_type=17` (term slug `eaa-accessibility`).  
    - Scrive i campi ACF elencati in [Mapping WordPress](#mapping-wordpress).
11. **Log Upsert Result WP**  
    - Aggiorna l’azienda (`acf[analysis_last_*]`) con esito e timestamp ISO.  
12. **Compose Upsert Result** → **Return Status**  
    - Restituisce `{"ok":bool,"status":int,"message":string}` al chiamante.  

## Contratto JSON dell’Agente di Sintesi

L’agente deve produrre JSON valido con la seguente struttura. Il parser accetta stringhe/array e rimuove duplicati numerando i conteggi.

| Campo | Tipo | Note |
| --- | --- | --- |
| `riassunto` | string ≤ 600 char | Sintesi per CEO, include punteggio e rischio. |
| `report_discorsivo` | string 2000–3000 char | Versione “cliente” del report completo (tono consulenziale). |
| `messaggi_principali` | array string | Max 5 bullet, ordine per impatto. |
| `promessa_valore` | string | Value proposition/accessibility promise. |
| `tono_di_voce` | string | Es. “Istituzionale sobrio”. |
| `differenziazione` | array string | Elementi distintivi sito vs competitor. |
| `coerenza_comunicativa` | string | Valutazione narrativa. |
| `punti_forza` / `punti_debolezza` | array string | Violazioni/asset principali. |
| `opportunita` | array string | Opportunità business/compliance. |
| `azioni_rapide` | array string | Quick wins (≤2 settimane) con ore/uomo. |
| `target_commerciali` | array string | Segmenti disabilità/mercato impattati. |
| `potenziale` | string | ROI + accesso mercati (ore/uomo, % mercato). |
| `idee_valore_agenzia` | array string | Servizi Perspect/Newwave con effort. |
| `domande_prospect` | array string | Domande per discovery con taglio legale/IT. |
| `attendibilita_dati` | object `{alert,bool; note,string}` | Limiti audit vs certificazione EN 301 549. *(Non ancora mappato in ACF.)* |
| `rischi_mitigazioni` | array `{rischio, mitigazione}` | Lista blocker + remediation ore/uomo. *(Non ancora mappato in ACF.)* |
| `priorita_temporali` | object `{entro_30_giorni[], entro_90_giorni[], entro_12_mesi[]}` | Roadmap con effort. *(Non ancora mappato in ACF.)* |
| `prove_trust` | object `{team_visibile,bool; case_study,bool; testimonianze,bool; certificazioni[]}` | Stato trust signals. *(Non ancora mappato in ACF.)* |
| `fonti` | array `{titolo?, url}` | Include Wave/Lighthouse + normative. *(Non ancora mappato in ACF.)* |
| `voto_qualita_analisi` | int 0–100 | Default 92 se dual audit completo. |
| `voto_qualita_dati` | int 0–100 | Valuta affidabilità dataset. |
| `ore_uomo_breakdown` | object | Breakdown totale/min/max per remediation. *(Richiesto da prompt ma **non** processato dal parser: valutare estensione futura.)* |

Il parser calcola automaticamente:

- `count_*` per ogni array (es. `count_punti_debolezza`),  
- `lunghezza_report_discorsivo`, `totale_elementi`,  
- `count_priorita_12_mesi`, `count_certificazioni`, `count_fonti`,  
- `output_agente_raw` (JSON originale serializzato per audit).

⚠️ Al momento il payload inviato a WordPress non include i campi contrassegnati come “Non ancora mappato” o `ore_uomo_breakdown`. Per renderli persistenti è necessario aggiornare sia il Field Group ACF che il nodo `Upsert WP`.

## Mapping WordPress

Il nodo `Upsert WP` invia i seguenti parametri (HTTP `POST` con `Content-Type: application/json; charset=utf-8`):

| Chiave `acf[...]` | Fonte nel parser | Note |
| --- | --- | --- |
| `riassunto` | `acf.riassunto` | |
| `punti_di_debolezza` | `acf.punti_debolezza` | Stringa/array → testo ACF. |
| `punti_di_forza` | `acf.punti_forza` | |
| `opportunita` | `acf.opportunita` | |
| `azioni_rapide` | `acf.azioni_rapide` | |
| `parent_company_id` | ID azienda | Reverse link. |
| `numero_punti_di_debolezza` | `count_punti_debolezza` | |
| `numero_punti_di_forza` | `count_punti_forza` | |
| `numero_opportunita` | `count_opportunita` | |
| `numero_azioni_rapide` | `count_azioni_rapide` | |
| `voto_qualita_analisi` | `voto_qualita_analisi` | |
| `analisy_perplexity_deep_research` | output Agente 1 | Report raw pre-revisione. |
| `revisione_analisi_completa` | output Agente 2 | Report raffinato. |
| `qualita_dati` | `voto_qualita_dati` | |
| `messaggi_principali` | `acf.messaggi_principali` | |
| `promessa_di_valore` | `acf.promessa_valore` | ⚠️ nel workflow è presente un refuso `acf[]promessa_di_valore` → va corretto in n8n. |
| `numero_messaggi_principali` | `count_messaggi_principali` | |
| `tono_di_voce` | `acf.tono_di_voce` | |
| `elementi_differenzianti` | `acf.differenziazione` | ⚠️ refuso n8n `acf[]elementi_differenzianti`. |
| `numero_elementi_differenzianti` | `count_differenziazione` | |
| `coerenza_comunicativa` | `acf.coerenza_comunicativa` | ⚠️ refuso n8n `acf[]coerenza_comunicativa`. |
| `target_commerciali` | `acf.target_commerciali` | |
| `numero_target_commerciali` | `count_target_commerciali` | ⚠️ refuso `acf[]numero_target_commerciali`. |
| `idee_di_valore_perspect` | `acf.idee_valore_agenzia` | |
| `numero_idee_di_valore` | `count_idee_valore_agenzia` | |
| `domande_prospect` | `acf.domande_prospect` | |
| `numero_domande` | `count_domande_prospect` | |
| `rischi` | `acf.rischi_mitigazioni` → attualmente serializzato come testo | Valutare campo repeater dedicato. |
| `numero_rischi` | `count_rischi_mitigazioni` | |
| `priorita_temporali` | `acf.priorita_temporali` | Salva JSON serializzato. |
| `report_discorsivo` | `acf.report_discorsivo` | Nuovo campo per versione cliente. |

> **Nota:** Il workflow presume che il Field Group `Analisi` contenga tutti i campi sopra elencati. Con l’export del 03/11/2025 i refusi `acf[]...` devono essere corretti al più presto per garantire la persistenza dei dati.

## Risposte & Logging

- **Risposta HTTP:** dal nodo `Return Status` → `200` con body `{"ok":true,"status":200,"message":"OK"}` (o relativi errori).  
- **Timeouts:** `Wave` & `Microlink` 60s; richieste WP 30s.  
- **Log su azienda:** `analysis_last_status_code`, `analysis_last_message`, `analysis_last_at` (ISO string).

## Considerazioni Operative & Rischi

- **Quota Wave API:** chiave `S151MWAV5944` ha rate limit (1 req/s, 5000/mese). Gestire backoff in caso di 429.
- **Microlink:** l’endpoint può fallire su siti che bloccano user agent generici. Il workflow non gestisce fallback manuali.
- **Campi non persistiti:** `attendibilita_dati`, `prove_trust`, `fonti`, `ore_uomo_breakdown` restano nel payload ma non vengono salvati; pianificare estensione ACF + Upsert.
- **Refusi nel body parametro:** correggere in n8n i nomi `acf[]...` per evitare perdita dati.
- **Agent Type:** assicurarsi che il termine `eaa-accessibility` esista nella tassonomia `agent_type` (slug e meta `webhook=/webhook/eaa-accessibility-agent`).
- **Compliance messaging:** tutti i costi sono espressi in ore/uomo; vietato introdurre valori economici diretti (controllo manuale consigliato).

## Endpoint di Test Rapido

```bash
curl -X POST https://automation.perspect.it/webhook/eaa-accessibility-agent \\
  -H 'Content-Type: application/json' \\
  -d '{"company_id": 1234}'
```

Controllare i log n8n e l’analisi su WordPress (`/wp-admin/post.php?post={analysis_id}&action=edit`) per verificare mappatura campi e tassonomia.
