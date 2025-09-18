<?php
class CFB_Dashboard {
    public function render_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'cfb_forms';
        $forms = $wpdb->get_results( "SELECT id, title, shortcode FROM {$table} ORDER BY id DESC" );
        ?>
        <div class="wrap cfb-dashboard">
            <h1>⚡ Form Builder Dashboard</h1>
            <p class="welcome-msg">Welcome to your dashboard 🎉 — manage your forms, view stats, or create a new form!</p>

            <div class="cfb-cards">
                <div class="cfb-card card-1">
                    <h2>Total Forms</h2>
                    <p class="cfb-number"><?php echo is_array($forms) ? count($forms) : 0; ?></p>
                    <a href="?page=cfb-contact" class="button button-primary">Create New</a>
                </div>
                <div class="cfb-card card-2">
                    <h2>Total Entries</h2>
                    <p class="cfb-number">124</p>
                    <a href="?page=cfb-entries" class="button">View Entries</a>
                </div>
                <div class="cfb-card card-3">
                    <h2>Quick Settings</h2>
                    <p>Customize global options for all forms.</p>
                    <a href="?page=cfb-settings" class="button">Go to Settings</a>
                </div>
                <div class="cfb-card card-4">
                    <h2>Need Help?</h2>
                    <p>Check documentation or contact support.</p>
                    <a href="https://example.com/docs" target="_blank" class="button">Docs</a>
                </div>
            </div>

            <div class="cfb-forms-list" style="margin-top:40px;">
                <h2>All Forms</h2>
                <?php if ( ! empty( $forms ) && is_array( $forms ) ): ?>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Shortcode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $forms as $form ): ?>
                            <tr>
                                <td><?php echo esc_html( $form->title ?: 'Untitled' ); ?></td>
                                <td><code><?php echo esc_html( $form->shortcode ?: sprintf('[custom_form id="%d"]', (int) $form->id ) ); ?></code></td>
                                <td><a href="?page=cfb-view-data&id=<?php echo esc_attr( (int) $form->id ); ?>" class="button">View Data</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No forms found.</p>
                <?php endif; ?>
            </div>
        </div>

        <style>
            .cfb-dashboard {
                font-family: "Segoe UI", sans-serif;
            }
            .cfb-dashboard h1 {
                margin-bottom: 10px;
                font-size: 26px;
                color: #333;
            }
            .cfb-dashboard .welcome-msg {
                font-size: 16px;
                margin-bottom: 25px;
                color: #555;
            }
            .cfb-forms-list h2 {
                margin-top: 30px;
                font-size: 22px;
            }
            .cfb-forms-list table {
                margin-top: 10px;
            }
            /* Card container */
            .cfb-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            /* Base card style */
            .cfb-card {
                padding: 20px;
                border-radius: 15px;
                color: #fff;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .cfb-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
            }
            .cfb-card h2 {
                margin-top: 0;
                font-size: 20px;
            }
            .cfb-card .cfb-number {
                font-size: 32px;
                font-weight: bold;
                margin: 10px 0;
            }
            /* Gradient colors */
            .cfb-card.card-1 { background: linear-gradient(135deg, #667eea, #764ba2); }
            .cfb-card.card-2 { background: linear-gradient(135deg, #f7971e, #ffd200); color:#222; }
            .cfb-card.card-3 { background: linear-gradient(135deg, #43cea2, #185a9d); }
            .cfb-card.card-4 { background: linear-gradient(135deg, #ff512f, #dd2476); }
            .cfb-card a.button {
                margin-top: 10px;
                display: inline-block;
                background: rgba(255,255,255,0.2);
                border: none;
                color: #fff;
                font-weight: bold;
                padding: 8px 15px;
                border-radius: 8px;
                transition: background 0.3s ease;
            }
            .cfb-card a.button:hover {
                background: rgba(255,255,255,0.4);
            }
            .cfb-card.card-2 a.button {
                color:#333;
                background: rgba(0,0,0,0.1);
            }
            .cfb-card.card-2 a.button:hover {
                background: rgba(0,0,0,0.2);
            }
        </style>
        <?php
    }
}
