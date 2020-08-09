<?php
/** @var \nigiri\exceptions\Exception $exception */

$output = [
    'code' => $exception->getCode(),
    'message' => $exception->getMessage()
];

if(\nigiri\Site::getParam(NIGIRI_PARAM_DEBUG)){
    $output['detail'] = $exception->getInternalError();
}

echo json_encode($output);
