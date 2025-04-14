<?php

class openai_tags_review_page
{
    public function load_module($directory, $urltoroot)
    {
        // No setup needed here for now
    }

    public function match_request($request)
    {
        return $request === 'tag-review';
    }

    public function process_request($request)
    {
        if (qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
            return array(
                'title' => 'Access Denied',
                'error' => 'You must be an admin to view this page.',
            );
        }

        $content = qa_content_prepare();
        $content['title'] = 'Pending Tag Suggestions';

        // Fetch pending suggestions
        $results = qa_db_read_all_assoc(qa_db_query_sub(
            'SELECT a.id,a.suggested_tags,b.title,b.tags,a.postid FROM ^tag_suggestions a,^posts b WHERE a.postid=b.postid and a.status IS NULL ORDER BY a.created DESC LIMIT 50'
        ));

        $html = '<table class="qa-form-tall-table" style="width: 100%">';
        $html .= '<tr><th>Post ID</th><th>Title</th><th>Tags</th><th>Suggested Tags</th><th>Actions</th></tr>';

        foreach ($results as $row) {
            $postid = $row['postid'];
            $suggested_tags = htmlspecialchars($row['suggested_tags']);
            $original_tags = htmlspecialchars($row['tags']);

            $html .= "<tr>
                <td><a href='" . qa_q_path_html($postid, '', true) . "' target='_blank'>$postid</a></td>
                <td>" . qa_html($row['title']) . "</td>
                <td>$original_tags</td>
                <td><form method='post' action='./tag-review'>
                    <input type='hidden' name='reviewid' value='{$row['id']}'>
                    <input type='hidden' name='postid' value='{$postid}'>
                    <input type='text' name='suggested_tags' value='{$suggested_tags}' style='width:200px'>
                    <button type='submit' name='approve'>Approve</button>
                    <button type='submit' name='reject'>Reject</button>
                </form></td>
            </tr>";
        }

        $html .= '</table>';

        $content['custom'] = $html;

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN) {
            $id = (int)qa_post_text('reviewid');
            $postid = (int)qa_post_text('postid');
            $tags = qa_post_text('suggested_tags');

            if (isset($_POST['approve'])) {
                // Update the original post's tags
                qa_db_query_sub(
                    'UPDATE ^posts SET tags=$ WHERE postid=#',
                    $tags, $postid
                );
                qa_db_query_sub(
                    'UPDATE ^tag_suggestions SET status="approved", suggested_tags=$ WHERE id=#',
                    $tags, $id
                );
            } elseif (isset($_POST['reject'])) {
                qa_db_query_sub(
                    'UPDATE ^tag_suggestions SET status="rejected" WHERE id=#',
                    $id
                );
            }

            qa_redirect('tag-review');
        }

        return $content;
    }
}

