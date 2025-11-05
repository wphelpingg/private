<?php
// Your user token (the one you want to check)
$inputToken = 'paste_your_token_here';

// Your App ID and App Secret
$appId = 'paste_your_app_id_here';
$appSecret = 'paste_your_app_secret_here';

// Generate app access token in format: app_id|app_secret
$appAccessToken = $appId . '|' . $appSecret;

// Build the API URL
$url = "https://graph.facebook.com/debug_token?input_token={$inputToken}&access_token={$appAccessToken}";

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Request Error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);


 echo '<pre>'; print_r($data); echo '</pre>';
// Check if token is valid
if (isset($data['data']['is_valid']) && $data['data']['is_valid']) {
    echo "✅ Token is valid.\n";
    echo "Token expires at: " . date('Y-m-d H:i:s', $data['data']['expires_at']) . "\n";
} else {
    echo "❌ Token is invalid or expired.\n";
    if (isset($data['data']['error'])) {
        echo "Error: " . $data['data']['error']['message'] . "\n";
    }
}

?>
