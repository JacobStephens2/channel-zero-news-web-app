<?php
 
require 'vendor/autoload.php';
require '../environmentVariables.php';
 
$client = OpenAI::client(OPENAI_API_KEY);

$prompt = <<<TEXT
Extract the requirements for this job offer as a list.
 
"We are seeking a PHP web developer to join our team.
The ideal candidate will have experience with
PHP, MySQL, HTML, CSS, and JavaScript.
They will be responsible for developing
and managing web applications and working
with a team of developers to create
high-quality and innovative software.
The salary for this position is negotiable
and will be based on experience."
TEXT;
 
$result = $client->completions()->create([
    // The most expensive model, but the best.
    'model' => 'text-davinci-002', 
    'prompt' => $prompt,
]);
 
echo '$result[\'choices\'][0][\'text\']: ' . "\n";
echo $result['choices'][0]['text'];

echo "\n";

echo '$result: ' . "\n";
print_r($result);

?>