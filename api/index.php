<?php

// Vercel runs PHP via Serverless Functions under /api.
// Route all requests to the main app entrypoint.

require __DIR__ . '/../index.php';

