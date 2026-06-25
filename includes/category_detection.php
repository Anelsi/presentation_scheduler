<?php

function allowedPresentationCategories()
{
    return ["Cloud", "AI", "Web", "Security"];
}

function presentationCategoryKeywords()
{
    return [
        "AI" => [
            "ai",
            "artificial intelligence",
            "machine learning",
            "llm",
            "neural",
            "prompt",
            "chatbot",
            "openai",
            "изкуствен интелект",
            "машинно обучение"
        ],
        "Cloud" => [
            "cloud",
            "docker",
            "container",
            "containers",
            "kubernetes",
            "aws",
            "azure",
            "deployment",
            "devops",
            "облак",
            "контейнери",
            "докер"
        ],
        "Web" => [
            "web",
            "php",
            "html",
            "css",
            "javascript",
            "api",
            "frontend",
            "backend",
            "form",
            "уеб",
            "сайт",
            "форма"
        ],
        "Security" => [
            "security",
            "password",
            "hashing",
            "encryption",
            "authentication",
            "xss",
            "sql injection",
            "vulnerability",
            "сигурност",
            "парола",
            "криптиране",
            "уязвимост"
        ]
    ];
}

function normalizeTextForCategoryMatching($value)
{
    if (function_exists("mb_strtolower")) {
        return mb_strtolower($value, "UTF-8");
    }

    return strtolower($value);
}

function normalizePresentationCategory($category)
{
    foreach (allowedPresentationCategories() as $allowedCategory) {
        if (strtolower($category) === strtolower($allowedCategory)) {
            return $allowedCategory;
        }
    }

    return null;
}

function detectPresentationCategory($title, $description)
{
    $text = normalizeTextForCategoryMatching($title . " " . $description);
    $scores = [];

    foreach (presentationCategoryKeywords() as $category => $keywords) {
        $scores[$category] = 0;

        foreach ($keywords as $keyword) {
            if (strpos($text, normalizeTextForCategoryMatching($keyword)) !== false) {
                $scores[$category]++;
            }
        }
    }

    arsort($scores);

    $bestCategory = array_key_first($scores);
    $bestScore = $scores[$bestCategory] ?? 0;

    if ($bestScore === 0) {
        return null;
    }

    $matchingCategories = array_filter($scores, function ($score) use ($bestScore) {
        return $score === $bestScore;
    });

    if (count($matchingCategories) > 1) {
        return null;
    }

    return $bestCategory;
}
