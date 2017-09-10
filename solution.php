<?php
// Marissa Novak
// marissa.novak3@gmail.com

function parse_request($request, $secret)
{
    $request = strtr($request, '-_', '+/');
    $parts = explode('.', $request);

    if (count($parts) < 2) {
    	return false;
    }
    $signature = base64_decode($parts[0]);
    $payload = base64_decode($parts[1]);

    // check that the signature matches
    if (hash_hmac('sha256', $payload, $secret, true) == $signature) {
        return json_decode($payload,true);
    }

    return false;
}

function dates_with_at_least_n_scores($pdo, $n)
{
    $sql = "
    SELECT date 
    FROM scores 
    GROUP BY date 
    HAVING COUNT(date) >= $n 
    ORDER BY date DESC
    ";

    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
}

function users_with_top_score_on_date($pdo, $date)
{
    $sql = "
    SELECT user_id,score
    FROM scores
    WHERE score = (
	    SELECT MAX(score) 
	    FROM scores 
	    WHERE date = '$date'
    ) 
    AND date = '$date'
    ";
    
    return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
}

function times_user_beat_overall_daily_average($pdo, $user_id)
{
    $sql = "
    SELECT COUNT(*)
    FROM scores
    WHERE score > (
	    SELECT AVG(score) 
	    FROM scores
    ) 
    AND user_id='$user_id'
    ";

    return $pdo->query($sql)->fetchColumn();
}
