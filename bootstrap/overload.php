<?php
// This must be loaded BEFORE Laravel boots

// Remove ALL limits at the earliest possible moment
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');
ini_set('max_input_time', '-1');
ini_set('post_max_size', '0');
ini_set('upload_max_filesize', '0');
ini_set('max_file_uploads', '10000');

// Disable PHP's built-in POST size check
ini_set('enable_post_data_reading', '0');
