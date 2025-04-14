<?php

if (!defined('QA_VERSION')) {
    header('Location: ../../');
    exit;
}

qa_register_plugin_module('module', 'qa-tag-review-admin.php', 'qa_tag_review_admin', 'Tag Review Admin');
qa_register_plugin_module('event', 'qa-openai-tags-event.php', 'openai_tags_event', 'Tag Review Event');

qa_register_plugin_module(
    'page',
    'qa-openai-tags-page.php',
    'openai_tags_review_page',
    'OpenAI Tag Review Page'
);

