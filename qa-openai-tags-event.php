<?php

class openai_tags_event {

    function process_event($event, $userid, $handle, $cookieid, $params) {
        if (($event !== 'q_post') && ($event !== 'q_edit')) return;

        $postid = (int)$params['postid'];
        $title = $params['title'];
        $tags = $params['tags'];

	$query = "select title,content from ^posts where postid = #";
	$result = qa_db_read_one_assoc(qa_db_query_sub($query, $postid), true);
	$title = $result['title'];
	$content = $result['content'];
	
	$prompt = "Suggest appropriate tags for the following question:\nTitle: $title\n Full Text: $content\nCurrent Tags: " . $tags;
        $suggested = qa_tag_review_call_openai($prompt);

        if ($suggested) {
            qa_db_query_sub(
                'INSERT INTO ^tag_suggestions (postid, suggested_tags, created) VALUES (#, $, NOW())',
                $postid, $suggested
            );
	}
	file_put_contents("/tmp/openaiout.txt", "$prompt...$suggested", FILE_APPEND | LOCK_EX);
    }
}

function qa_tag_review_call_openai($message) {
    $apikey = qa_opt('openai_api_key');
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        "model" => qa_opt('openai_model'),
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant who suggests appropriate tags for Q&A posts."],
            ["role" => "user", "content" => $message]
        ]
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer $apikey"
            ],
            'content' => json_encode($data),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result !== false) {
        $response = json_decode($result, true);
        return $response['choices'][0]['message']['content'] ?? null;
    }

    return null;
}

