<style>
    .report-print-header,
    .report-print-footer {
        display: none;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        html,
        body {
            background: #fff !important;
            color: #111 !important;
            font-size: 11px;
        }

        #layout-navbar,
        #layout-menu,
        #template-customizer,
        .content-footer,
        .no-print,
        .btn,
        form.report-filter-form {
            display: none !important;
        }

        .layout-wrapper,
        .layout-container,
        .layout-page,
        .content-wrapper,
        .container-xxl,
        .container-p-y {
            display: block !important;
            margin: 0 !important;
            max-width: 100% !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
        }

        .card-header {
            border: 0 !important;
            padding: 0 0 8px 0 !important;
        }

        .report-print-header {
            display: block !important;
            margin-bottom: 12px;
        }

        .report-print-footer {
            display: flex !important;
            justify-content: flex-end;
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .report-signature-box {
            text-align: center;
            width: 220px;
        }

        .report-signature-line {
            border-top: 1px solid #111;
            margin-top: 64px;
            padding-top: 4px;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table.table {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.table th,
        table.table td {
            border: 1px solid #333 !important;
            color: #111 !important;
            padding: 4px 6px !important;
        }

        table.table thead th {
            background: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge {
            border: 1px solid #666;
            color: #111 !important;
            background: transparent !important;
        }
    }
</style>
