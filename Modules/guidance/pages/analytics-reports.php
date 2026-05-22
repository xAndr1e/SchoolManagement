
    <!-- MODULE CONTENT (from module-container.css) -->
    <div class="container module-content">
        <!-- ════════════════════════════════════════
             PAGE › ANALYTICS
        ════════════════════════════════════════ -->
        <div class="container" id="page-analytics">
            <div class="page-actions">
                <button class="btn"><i class="fa-solid fa-print"></i> Print</button>
                <button class="btn btn-gold"><i class="fa-solid fa-file-pdf"></i> Export PDF</button>
            </div>

            <div class="metrics-grid" style="grid-template-columns:repeat(3,1fr);">
                <div class="metric-card green">
                    <div class="metric-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="metric-label">Cases Resolved</div>
                    <div class="metric-value">214</div>
                    <div class="metric-change up">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size:10px;"></i>
                        18% vs last year
                    </div>
                </div>
                <div class="metric-card blue">
                    <div class="metric-icon"><i class="fa-solid fa-chart-simple"></i></div>
                    <div class="metric-label">Avg Sessions / Student</div>
                    <div class="metric-value">3.2</div>
                    <div class="metric-change neutral">Target: 4.0</div>
                </div>
                <div class="metric-card gold">
                    <div class="metric-icon"><i class="fa-regular fa-star"></i></div>
                    <div class="metric-label">Satisfaction Rate</div>
                    <div class="metric-value">88%</div>
                    <div class="metric-change up">
                        <i class="fa-solid fa-arrow-trend-up" style="font-size:10px;"></i>
                        4% this semester
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Cases by Type</div>
                    <div id="caseTypeList"></div>
                </div>
                <div class="card">
                    <div class="card-title"><i class="fa-solid fa-graduation-cap"></i> At-Risk by Grade Level</div>
                    <div id="gradeRiskList"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="fa-solid fa-chart-line"></i> Monthly Case Trend</div>
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#2563eb;"></div> New Cases
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#16a34a;"></div> Resolved
                    </div>
                </div>
                <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
            </div>
        </div><!-- /page-analytics -->