<?php

class qa_tag_review_admin {

    function option_default($option) {
        return null;
    }


     // ✅ Q2A will call this when plugin is activated
    public static function init_queries($tablesexisting)
    {
        if (!in_array(qa_db_add_table_prefix('tag_suggestions'), $tablesexisting)) {
            return
                'CREATE TABLE IF NOT EXISTS ^tag_suggestions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    postid INT NOT NULL,
                    suggested_tags TEXT,
                    status ENUM("approved","rejected") DEFAULT NULL,
                    created DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
        }

        return null;
    }

    // ✅ Admin form for setting API key and model
    public function admin_form(&$qa_content)
    {
        // Save settings
        $saved = false;

        if (qa_clicked('openai_save_button')) {
            qa_opt('openai_api_key', qa_post_text('openai_api_key'));
            qa_opt('openai_model', qa_post_text('openai_model'));
            $saved = true;
        }

        return array(
            'ok' => $saved ? 'OpenAI settings saved.' : null,

            'fields' => array(
                array(
                    'label' => 'OpenAI API Key',
                    'type' => 'password',
                    'value' => qa_opt('openai_api_key'),
                    'tags' => 'name="openai_api_key"',
                ),
                array(
                    'label' => 'OpenAI Model (e.g. gpt-4, gpt-3.5-turbo)',
                    'type' => 'text',
                    'value' => qa_opt('openai_model'),
                    'tags' => 'name="openai_model"',
                ),
            ),

            'buttons' => array(
                array(
                    'label' => 'Save Settings',
                    'tags' => 'name="openai_save_button"',
                ),
            ),
        );
    }
}

