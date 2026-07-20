<?php

try {
    // Laravel සැබෑ entrypoint එක පූරණය කිරීම
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    // 💡 සර්වර් එක ඇතුලේ සිදුවන සැබෑම වැරැද්ද (Fatal Error) බ්‍රව්සර් එකේ පෙන්වීම
    echo "<div style='background:#fef2f2; border:1px solid #ef4444; padding:30px; border-radius:12px; font-family:sans-serif; max-width:800px; margin:40px auto; color:#1e1e2d;'>";
    echo "<h2 style='color:#dc2626; margin-top:0;'>⚠️ Laravel Fatal Boot Error</h2>";
    echo "<p><strong>Error Message:</strong> <span style='color:#ef4444;'>" . htmlspecialchars($e->getMessage()) . "</span></p>";
    echo "<p><strong>File Path:</strong> " . htmlspecialchars($e->getFile()) . " on line <strong>" . $e->getLine() . "</strong></p>";
    echo "<h3 style='margin-bottom:8px; margin-top:20px;'>Stack Trace:</h3>";
    echo "<pre style='background:#1e1e2d; color:#86efac; padding:15px; border-radius:8px; overflow-x:auto; font-size:0.85rem; line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit(1);
}