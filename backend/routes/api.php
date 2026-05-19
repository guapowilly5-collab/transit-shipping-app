<?php

$conn = getConnection();

echo json_encode([
    'success' => true,
    'message' => 'API Lome Marine operationnelle',
    'database' => 'connectee'
]); 
