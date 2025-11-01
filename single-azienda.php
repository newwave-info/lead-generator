<?php
get_header();

if (have_posts()):
    while (have_posts()):

        the_post();
        $post_id = get_the_ID();

        // Campi ACF principali
        $status = get_field("status", $post_id);

        $key_factor_analisi = get_field("key_factor_analisi", $post_id);
        $motivazioni_status = get_field("motivazioni_status", $post_id);
        $analisy_perplexity_deep_research = get_field(
            "analisy_perplexity_deep_research",
            $post_id
        );
        $perplexity_analysis_markup = $analisy_perplexity_deep_research;

        $growth_stage = get_field("growth_stage", $post_id);
        $estimated_marketing_budget = get_field(
            "estimated_marketing_budget",
            $post_id
        );
        $analysis_date = get_field("analysis_date", $post_id); // formato d/m/Y g:i a
        $partita_iva = get_field("partita_iva", $post_id);
        $address = get_field("address", $post_id);
        $phone = get_field("phone", $post_id);
        $website = get_field("website", $post_id);
        $domain = get_field("domain", $post_id);
        $business_type = get_field("business_type", $post_id);
        $sector_specific = get_field("sector_specific", $post_id);
        $employee_count_est = get_field("employee_count_est", $post_id);

        $estimated_annual_revenue = get_field(
            "estimated_annual_revenue",
            $post_id
        );
        $estimated_ebitda_percentage = get_field(
            "estimated_ebitda_percentage",
            $post_id
        );
        $confidence = get_field("confidence", $post_id);
        $budget_tier = get_field("budget_tier", $post_id);
        $budget_qualified = (bool) get_field("budget_qualified", $post_id);

        $campaign_name = get_field("campaign_name", $post_id);
        $data_completeness = get_field("data_completeness", $post_id);
        $source = get_field("source", $post_id);
        $query_used = get_field("query_used", $post_id);
        $discovery_id = get_field("discovery_id", $post_id);
        $run_id = get_field("run_id", $post_id);

        $place_id = get_field("place_id", $post_id);
        $rating = get_field("rating", $post_id);
        $reviews = get_field("reviews", $post_id);
        $discovery_date = get_field("discovery_date", $post_id); // d/m/Y
        $discovery_time = get_field("discovery_time", $post_id); // g:i a

        $scalar_fields = [
            "status",
            "growth_stage",
            "estimated_marketing_budget",
            "analysis_date",
            "partita_iva",
            "address",
            "phone",
            "website",
            "domain",
            "business_type",
            "sector_specific",
            "employee_count_est",
            "estimated_annual_revenue",
            "estimated_ebitda_percentage",
            "confidence",
            "budget_tier",
            "campaign_name",
            "data_completeness",
            "source",
            "query_used",
            "discovery_id",
            "run_id",
            "place_id",
            "rating",
            "reviews",
            "discovery_date",
            "discovery_time",
        ];

        foreach ($scalar_fields as $field_key) {
            $$field_key = psip_theme_normalize_scalar($$field_key);
        }

        if ($perplexity_analysis_markup) {
            $perplexity_analysis_markup = preg_replace_callback(
                "/^\\s*#{1,6}\\s*(.*?)\\s*#*\\s*$/m",
                static function ($matches) {
                    $heading_text = trim($matches[1]);
                    if ($heading_text === "") {
                        return $matches[0];
                    }

                    return "<strong>" . esc_html($heading_text) . "</strong>";
                },
                $perplexity_analysis_markup
            );
        }

        $industry_display = $business_type !== "" ? $business_type : "-";
        $subindustry_display = $sector_specific !== "" ? $sector_specific : "-";
        $headquarters_display = $address !== "" ? $address : "-";
        $status_display = $status !== "" ? $status : "-";
        $growth_stage_display = $growth_stage !== "" ? $growth_stage : "-";
        $budget_display = $estimated_marketing_budget !== ""
            ? $estimated_marketing_budget
            : "-";
        $confidence_display = $confidence !== "" ? $confidence : "-";

        $analysis_last_update = "-";
        if ($analysis_date !== "") {
            $analysis_dt = DateTime::createFromFormat(
                "d/m/Y g:i a",
                $analysis_date
            );
            if ($analysis_dt instanceof DateTimeInterface) {
                $analysis_last_update = $analysis_dt->format("Y-m-d H:i");
            } else {
                $analysis_last_update = $analysis_date;
            }
        }

        $is_verified = strtoupper($status_display) === "QUALIFIED";
        $confidence_hint = $confidence_display !== "-"
            ? 'Indice CRM: ' . $confidence_display
            : "In valutazione";

        $confidence_percent_display = null;
        if ($confidence_display !== "-") {
            if (
                preg_match(
                    '/([0-9]+(?:[\\.,][0-9]+)?)/',
                    $confidence_display,
                    $confidence_matches
                )
            ) {
                $confidence_numeric = (float) str_replace(
                    ",",
                    ".",
                    $confidence_matches[1]
                );
                if ($confidence_numeric <= 1) {
                    $confidence_numeric *= 100;
                }
                $confidence_percent_display = (int) round(
                    max(0, min(100, $confidence_numeric))
                );
            }
        }

        $data_completeness_percent = null;
        if ($data_completeness !== "") {
            if (is_numeric($data_completeness)) {
                $data_numeric = (float) $data_completeness;
            } elseif (
                preg_match(
                    '/([0-9]+(?:[\\.,][0-9]+)?)/',
                    $data_completeness,
                    $data_matches
                )
            ) {
                $data_numeric = (float) str_replace(
                    ",",
                    ".",
                    $data_matches[1]
                );
            } else {
                $data_numeric = null;
            }

            if (isset($data_numeric)) {
                if ($data_numeric <= 1) {
                    $data_numeric *= 100;
                }
                $data_completeness_percent = (int) round(
                    max(0, min(100, $data_numeric))
                );
            }
        }

        $hero_breadcrumbs = array_filter(
            [
                $source !== "" ? $source : null,
                $campaign_name !== "" ? $campaign_name : null,
            ],
            static function ($entry) {
                return $entry !== null;
            }
        );
        if (empty($hero_breadcrumbs)) {
            $hero_breadcrumbs = ["Lead Generator"];
        }
        $hero_breadcrumbs_text = implode(" · ", $hero_breadcrumbs);
        $confidence_primary_display = $confidence_percent_display !== null
            ? $confidence_percent_display . "%"
            : ($confidence_display !== "-" ? $confidence_display : "—");
        ?>
<main id="single_company" role="main">

    <section id="company_hero" class="company-hero">
        <div class="company-hero__gradient"></div>
        <div class="container-fluid">
            <div class="company-hero__content row gy-4 align-items-start">
                <div class="col-12 col-lg-7">
                    <div class="company-hero__identity d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                        <div class="company-hero__logo">
                            <img class="img-contain" src="https://api.microlink.io/?url=https%3A%2F%2F<?php echo esc_html(
                                $domain
                            ); ?>&palette=true&embed=logo.url" loading="lazy" />
                        </div>
                        <div class="company-hero__identity-text">
                            <div class="company-hero__breadcrumb small text-muted">
                                <?php echo esc_html($hero_breadcrumbs_text); ?>
                            </div>
                            <h1 class="company-hero__title"><?php the_title(); ?></h1>
                        </div>
                    </div>
                    <div class="company-hero__meta row g-3 mt-4">
                        <div class="col-12 col-sm-4">
                            <div class="company-hero__meta-card">
                                <span class="company-hero__meta-label">Industry</span>
                                <span class="company-hero__meta-value"><?php echo esc_html(
                                    $industry_display
                                ); ?></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="company-hero__meta-card">
                                <span class="company-hero__meta-label">Sub-industry</span>
                                <span class="company-hero__meta-value"><?php echo esc_html(
                                    $subindustry_display
                                ); ?></span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="company-hero__meta-card">
                                <span class="company-hero__meta-label">Headquarters</span>
                                <span class="company-hero__meta-value"><?php echo esc_html(
                                    $headquarters_display
                                ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5 col-xxl-4 offset-xxl-1">
                    <div class="company-rating-card">
                        <div class="company-rating-card__top">
                            <span class="company-rating-card__label">Qualifica</span>
                            <?php if ($is_verified): ?>
                                <span class="company-rating-card__verified">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    Verificato
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="company-rating-card__status">
                            <span class="company-rating-card__status-pill <?php echo $is_verified
                                ? "is-verified"
                                : "is-pending"; ?>"><?php echo esc_html(
    $status_display
); ?></span>
                        </div>
                        <div class="company-rating-card__score">
                            <span class="company-rating-card__score-value"><?php echo esc_html(
                                $confidence_primary_display
                            ); ?></span>
                            <span class="company-rating-card__score-hint"><?php echo esc_html(
                                $confidence_hint
                            ); ?></span>
                        </div>
                        <ul class="company-rating-card__list">
                            <li>
                                <span>Status account</span>
                                <strong><?php echo esc_html($status_display); ?></strong>
                            </li>
                            <li>
                                <span>Stadio di crescita</span>
                                <strong><?php echo esc_html($growth_stage_display); ?></strong>
                            </li>
                            <li>
                                <span>Budget marketing</span>
                                <strong><?php echo esc_html($budget_display); ?></strong>
                            </li>
                            <li>
                                <span>Ultimo aggiornamento</span>
                                <strong><?php echo esc_html($analysis_last_update); ?></strong>
                            </li>
                        </ul>
                        <div class="company-rating-card__actions">
                            <?php if ($website !== ""): ?>
                                <a
                                    class="company-rating-card__button"
                                    href="<?php echo esc_url($website); ?>"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Visita sito
                                </a>
                            <?php endif; ?>
                            <a
                                class="company-rating-card__button company-rating-card__button--ghost"
                                href="#company_content"
                            >
                                Apri dettagli
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section><!--company_hero-->

    <section id="company_content">
        <div class="container-fluid">

            <div class="row gy-4">

                <!-- Colonna destra -->
                <div class="col-md-6 col-lg-5 col-xxl-4 order-md-last">
                    <aside class="company-sidebar">
                        <?php
                        $screen_home = get_field("screen_home");
                        if ($screen_home):
                            $image_data = wp_get_attachment_image_src(
                                $screen_home,
                                "full"
                            );
                        ?>
                            <div class="company-sidebar__card company-sidebar__card--media">
                                <div class="company-sidebar__card-header">
                                    <h2 class="company-sidebar__title">Website</h2>
                                    <a class="company-sidebar__link" href="<?php echo esc_url($image_data[0]); ?>" data-fancybox>
                                        Apri preview
                                    </a>
                                </div>
                                <div class="company-sidebar__media">
                                    <div class="macos-titlebar">
                                        <div class="macos-buttons">
                                            <span class="macos-btn macos-btn-close"></span>
                                            <span class="macos-btn macos-btn-minimize"></span>
                                            <span class="macos-btn macos-btn-maximize"></span>
                                        </div>
                                    </div>
                                    <figure class="figure figure-scroll" style="padding-top: 56.25%;">
                                        <img class="img-cover" src="<?php echo esc_url($image_data[0]); ?>" loading="lazy" />
                                    </figure>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="company-sidebar__card">
                            <h2 class="company-sidebar__title">Azioni rapide</h2>
                            <div class="company-sidebar__actions">
                                <?php if ($website): ?>
                                    <a class="company-sidebar__button" href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener">Visita sito</a>
                                <?php endif; ?>
                                <a class="company-sidebar__button company-sidebar__button--ghost" href="#table_agents">Tutte le analisi</a>
                            </div>
                        </div>

                        <div class="company-sidebar__card">
                            <h2 class="company-sidebar__title">Indicatori</h2>
                            <dl class="company-sidebar__meta">
                                <div>
                                    <dt>Completezza dati</dt>
                                    <dd><?php echo $data_completeness !== "" ? esc_html($data_completeness) . "%" : "-"; ?></dd>
                                </div>
                                <div>
                                    <dt>Fonte</dt>
                                    <dd><?php echo $source ? esc_html($source) : "-"; ?></dd>
                                </div>
                                <div>
                                    <dt>Data analisi</dt>
                                    <dd><?php echo esc_html($analysis_last_update); ?></dd>
                                </div>
                                <div>
                                    <dt>Status</dt>
                                    <dd><?php echo esc_html($status_display); ?></dd>
                                </div>
                                <div>
                                    <dt>Discovery ID</dt>
                                    <dd><?php echo $discovery_id ? esc_html($discovery_id) : "-"; ?></dd>
                                </div>
                                <div>
                                    <dt>Run ID</dt>
                                    <dd><?php echo $run_id ? esc_html($run_id) : "-"; ?></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="company-sidebar__card company-sidebar__card--accordion">
                            <div class="accordion company-sidebar__accordion" id="accordionDatiAzienda">
                                <!-- Accordion Item 1: Anagrafica azienda -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAnagrafica" aria-expanded="false" aria-controls="collapseAnagrafica">
                                            Anagrafica azienda
                                        </button>
                                    </h2>
                                    <div id="collapseAnagrafica" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="row gy-4">
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Partita IVA</div>
                                                    <div><?php echo $partita_iva
                                                        ? esc_html($partita_iva)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Telefono</div>
                                                    <div><?php echo $phone
                                                        ? esc_html($phone)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Dominio</div>
                                                    <div><?php echo $domain
                                                        ? esc_html($domain)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="small text-muted">Indirizzo</div>
                                                    <div><?php echo $address
                                                        ? nl2br(esc_html($address))
                                                        : "-"; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accordion Item 2: Dati economici -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEconomici" aria-expanded="false" aria-controls="collapseEconomici">
                                            Dati economici
                                        </button>
                                    </h2>
                                    <div id="collapseEconomici" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="row gy-4">
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Fatturato stimato</div>
                                                    <div><?php echo $estimated_annual_revenue
                                                        ? esc_html($estimated_annual_revenue)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">EBITDA % stimato</div>
                                                    <div><?php echo $estimated_ebitda_percentage !== ""
                                                        ? esc_html($estimated_ebitda_percentage)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Budget Marketing</div>
                                                    <div><?php echo $estimated_marketing_budget
                                                        ? esc_html($estimated_marketing_budget)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Tier Budget</div>
                                                    <div><?php echo $budget_tier
                                                        ? esc_html($budget_tier)
                                                        : "-"; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accordion Item 3: Campagna -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCampagna" aria-expanded="false" aria-controls="collapseCampagna">
                                            Campagna
                                        </button>
                                    </h2>
                                    <div id="collapseCampagna" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="row gy-4">
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Nome Campagna</div>
                                                    <div><?php echo $campaign_name
                                                        ? esc_html($campaign_name)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Status campagna</div>
                                                    <div><?php echo $status_display; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Query utilizzata</div>
                                                    <div><?php echo $query_used
                                                        ? esc_html($query_used)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Discovery ID</div>
                                                    <div><?php echo $discovery_id
                                                        ? esc_html($discovery_id)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Run ID</div>
                                                    <div><?php echo $run_id
                                                        ? esc_html($run_id)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Completezza Dati</div>
                                                    <div><?php echo $data_completeness !== ""
                                                        ? esc_html($data_completeness) . "%"
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Fonte</div>
                                                    <div><?php echo $source
                                                        ? esc_html($source)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Data Analisi</div>
                                                    <div><?php echo esc_html($analysis_last_update); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accordion Item 4: Dati Google -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGoogle" aria-expanded="false" aria-controls="collapseGoogle">
                                            Dati Google
                                        </button>
                                    </h2>
                                    <div id="collapseGoogle" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="row gy-4">
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Rating</div>
                                                    <div><?php echo $rating !== ""
                                                        ? esc_html($rating)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Recensioni</div>
                                                    <div><?php echo $reviews !== ""
                                                        ? esc_html($reviews)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Data Discovery</div>
                                                    <div><?php echo $discovery_date
                                                        ? esc_html($discovery_date)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="small text-muted">Ora Discovery</div>
                                                    <div><?php echo $discovery_time
                                                        ? esc_html($discovery_time)
                                                        : "-"; ?></div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="small text-muted">Place ID</div>
                                                    <div><?php echo $place_id
                                                        ? esc_html($place_id)
                                                        : "-"; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <!-- Colonna sinistra: dettaglio -->
                <div class="col-md-6 col-lg-7 col-xxl-8">

                        <div class="company-insights">
                            <div class="company-insights__card">
                                <span class="company-insights__label">Fatturato stimato</span>
                                <span class="company-insights__value"><?php echo $estimated_annual_revenue !== ""
                                    ? esc_html($estimated_annual_revenue)
                                    : "-"; ?></span>
                                <span class="company-insights__caption">Annual revenue</span>
                            </div>
                            <div class="company-insights__card">
                                <span class="company-insights__label">Dipendenti stimati</span>
                                <span class="company-insights__value"><?php echo $employee_count_est !== ""
                                    ? esc_html($employee_count_est)
                                    : "-"; ?></span>
                                <span class="company-insights__caption">Team size</span>
                            </div>
                            <div class="company-insights__card">
                                <span class="company-insights__label">Budget marketing</span>
                                <span class="company-insights__value"><?php echo $estimated_marketing_budget !== ""
                                    ? esc_html($estimated_marketing_budget)
                                    : "-"; ?></span>
                                <span class="company-insights__caption">Forecasted spend</span>
                            </div>
                            <div class="company-insights__card company-insights__card--chart">
                                <span class="company-insights__label">Confidence score</span>
                                <?php if ($confidence_percent_display !== null): ?>
                                    <div class="company-insights__bar">
                                        <span style="width: <?php echo esc_attr(
                                            (string) $confidence_percent_display
                                        ); ?>%;"></span>
                                    </div>
                                    <span class="company-insights__stat">
                                        <?php echo esc_html(
                                            (string) $confidence_percent_display
                                        ); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="company-insights__value"><?php echo esc_html(
                                        $confidence_display
                                    ); ?></span>
                                    <span class="company-insights__caption">Confidence rating</span>
                                <?php endif; ?>
                            </div>
                            <div class="company-insights__card company-insights__card--chart">
                                <span class="company-insights__label">Completezza dati</span>
                                <?php if ($data_completeness_percent !== null): ?>
                                    <div class="company-insights__bar company-insights__bar--alt">
                                        <span style="width: <?php echo esc_attr(
                                            (string) $data_completeness_percent
                                        ); ?>%;"></span>
                                    </div>
                                    <span class="company-insights__stat">
                                        <?php echo esc_html(
                                            (string) $data_completeness_percent
                                        ); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="company-insights__value">
                                        <?php echo $data_completeness !== ""
                                            ? esc_html($data_completeness)
                                            : "—"; ?>
                                    </span>
                                    <span class="company-insights__caption">Reported by source</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="text">
                                <h2 class="mb-2">Key factor analisi azienda</h2>
                                <div>
                                   <?php echo $key_factor_analisi
                                       ? esc_html($key_factor_analisi)
                                       : "-"; ?>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="mb-5">
                            <div class="text">
                                <h2 class="mb-2">Analisi azienda completa</h2>
                                <div class="accordion" id="accordionAnalisiPerplexity">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseAnalisiPerplexity"
                                                aria-expanded="false"
                                                aria-controls="collapseAnalisiPerplexity"
                                            >
                                                Apri analisi completa
                                            </button>
                                        </h2>
                                        <div
                                            id="collapseAnalisiPerplexity"
                                            class="accordion-collapse collapse"
                                            data-bs-parent="#accordionAnalisiPerplexity"
                                        >
                                            <div class="accordion-body">
                                                <?php if (
                                                    $perplexity_analysis_markup
                                                ): ?>
                                                    <div class="analysis-perplexity-scroll">
                                                        <?php echo wpautop(
                                                            wp_kses_post(
                                                                $perplexity_analysis_markup
                                                            )
                                                        ); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div>-</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    <!-- Analisi correlate per agent type -->
                    <?php
                    $agents = psip_get_agents();
                    $analisi_for_company = psip_get_company_analyses($post_id);

                    $agent_total = count($agents);
                    $analysis_completed = 0;
                    $quality_sum = 0.0;
                    $quality_count = 0;

                    foreach ($analisi_for_company as $analysis_item) {
                        if (!is_array($analysis_item) || empty($analysis_item)) {
                            continue;
                        }

                        $analysis_completed++;
                        $analysis_id = $analysis_item["id"] ?? null;
                        if (!$analysis_id) {
                            continue;
                        }

                        $quality_raw = get_field(
                            "voto_qualita_analisi",
                            $analysis_id
                        );
                        $quality_normalized = psip_theme_normalize_scalar(
                            $quality_raw
                        );
                        if (
                            $quality_normalized !== "" &&
                            is_numeric($quality_normalized)
                        ) {
                            $quality_sum += (float) $quality_normalized;
                            $quality_count++;
                        }
                    }

                    $analysis_completion_rate = $agent_total > 0
                        ? round(($analysis_completed / $agent_total) * 100)
                        : 0;
                    $average_quality_display = $quality_count > 0
                        ? number_format($quality_sum / $quality_count, 1)
                        : "—";

                    // Mappa delle metriche con icone e nomi campi ACF aggiornati
                    $metrics_map = [
                        [
                            "label" => "Punti di forza",
                            "icon" => "dashicons-awards",
                            "field" => "numero_punti_di_forza",
                        ],
                        [
                            "label" => "Punti di debolezza",
                            "icon" => "dashicons-warning",
                            "field" => "numero_punti_di_debolezza",
                        ],
                        [
                            "label" => "Opportunità",
                            "icon" => "dashicons-thumbs-up",
                            "field" => "numero_opportunita",
                        ],
                        [
                            "label" => "Azioni rapide",
                            "icon" => "dashicons-star-filled",
                            "field" => "numero_azioni_rapide",
                        ],
                    ];
                    ?>

                    <?php if (!empty($agents)): ?>
                        <div id="table_agents" class="company-analyses">
                            <div class="company-analyses__header">
                                <div>
                                    <h2 class="company-analyses__title">Panoramica agent</h2>
                                    <p class="company-analyses__subtitle">Monitoraggio centralizzato delle analisi AI e delle priorità suggerite dai diversi agent.</p>
                                </div>
                                <div class="company-analyses__stats">
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Agent disponibili</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html((string) $agent_total); ?></span>
                                    </div>
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Analisi completate</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html((string) $analysis_completed); ?></span>
                                        <span class="company-analyses__stat-hint"><?php echo esc_html((string) $analysis_completion_rate); ?>%</span>
                                    </div>
                                    <div class="company-analyses__stat">
                                        <span class="company-analyses__stat-label">Qualità media</span>
                                        <span class="company-analyses__stat-value"><?php echo esc_html($average_quality_display); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="company-analyses__table-wrapper">
                                <table id="companyAgents" class="company-analyses-table display nowrap">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th class="th-score">Qualità</th>
                                            <?php foreach ($metrics_map as $metric): ?>
                                                <th data-bs-toggle="tooltip" title="<?php echo esc_attr($metric['label']); ?>">
                                                    <span class="dashicons <?php echo esc_attr($metric['icon']); ?>"></span>
                                                </th>
                                            <?php endforeach; ?>
                                            <th>Ultima esecuzione</th>
                                            <th>Azione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agents as $slug => $agent_config):
                                            $analysis_info = $analisi_for_company[$slug] ?? null;
                                            $has_analysis = $analysis_info !== null;
                                            $analysis_id = $analysis_info['id'] ?? null;
                                            $last_run_display = $analysis_info['last_run']['display'] ?? '';
                                            $last_run_relative = $analysis_info['last_run']['relative'] ?? '';

                                            $score_value = $has_analysis ? psip_theme_normalize_scalar(get_field('voto_qualita_analisi', $analysis_id)) : '';
                                            $score_numeric = ($score_value !== '' && is_numeric($score_value)) ? (float) $score_value : null;
                                            $score_color = $score_numeric !== null ? get_heatmap_color($score_numeric) : null;

                                            $status_label = $has_analysis ? 'Completata' : 'In attesa';
                                            $status_class = $has_analysis ? 'is-completed' : 'is-pending';
                                            ?>
                                            <tr>
                                                <td class="company-analyses-table__agent">
                                                    <span class="company-analyses-table__agent-icon"><span class="dashicons <?php echo esc_attr($agent_config['icon']); ?>"></span></span>
                                                    <div class="company-analyses-table__agent-info">
                                                        <a href="#analysis-tabs" class="company-analyses-table__agent-name"><?php echo esc_html($agent_config['name']); ?></a>
                                                        <span class="company-analyses-table__agent-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                                                    </div>
                                                </td>
                                                <td class="company-analyses-table__score"<?php echo $score_color ? ' style="--score-color:' . esc_attr($score_color) . ';"' : ''; ?>>
                                                    <?php if ($score_numeric !== null): ?>
                                                        <span class="company-analyses-table__score-badge"><?php echo esc_html(number_format($score_numeric, 1)); ?></span>
                                                    <?php else: ?>
                                                        <span class="company-analyses-table__score-badge company-analyses-table__score-badge--empty">—</span>
                                                    <?php endif; ?>
                                                </td>

                                            <?php foreach ($metrics_map as $metric):
                                                $value = $has_analysis ? psip_theme_normalize_scalar(get_field($metric['field'], $analysis_id)) : '';
                                                $has_metric_value = $value !== '';
                                                $display_value = $has_metric_value ? esc_html($value) : '—';
                                                $metric_class = $has_metric_value ? '' : ' company-analyses-table__metric-pill--empty';
                                            ?>
                                                <td class="company-analyses-table__metric" data-bs-toggle="tooltip" title="<?php echo esc_attr($agent_config['name'] . ': ' . $metric['label']); ?>">
                                                    <span class="company-analyses-table__metric-pill<?php echo $metric_class; ?>"><?php echo esc_html($display_value); ?></span>
                                                </td>
                                                <?php endforeach; ?>

                                                <td class="company-analyses-table__timestamp" title="<?php echo $last_run_relative ? esc_attr($last_run_relative) : ''; ?>">
                                                    <?php echo $last_run_display !== '' ? esc_html($last_run_display) : '—'; ?>
                                                </td>
                                                <td class="company-analyses-table__action">
                                                    <button class="company-analyses-table__action-btn" type="button" onclick="alert('Coming soon!')">
                                                        <?php echo $has_analysis ? 'Apri report' : 'Avvia analisi'; ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>



                </div>

            </div>

        </div>
    </section>
</main>

<?php get_template_part("inc/company-analysis-tabs"); ?>

<?php
    endwhile;
endif;

get_footer();
