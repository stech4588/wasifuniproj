<?php

use App\Support\ResponseMessage;

return [
    'internal_error' => ResponseMessage::make(
        500,
        'Internal Server Error'
    ),

    'name_exist' => ResponseMessage::make(
        403,
        ':module name already exists.',
        [':module' => 'Record'],
        [':module']
    ),

    'not_found' => ResponseMessage::make(
        404,
        ':module ID not found.',
        [':module' => 'Record'],
        [':module']
    ),

    'record_created' => ResponseMessage::make(
        200,
        ':module created successfully.',
        [':module' => 'Record'],
        [':module']
    ),

    'record_updated' => ResponseMessage::make(
        200,
        ':module updated successfully.',
        [':module' => 'Record'],
        [':module']
    ),

    'record_deleted' => ResponseMessage::make(
        200,
        ':module deleted successfully.',
        [':module' => 'Record'],
        [':module']
    ),

    'active' => ResponseMessage::make(
        200,
        ':module active successfully.',
        [':module' => 'Record'],
        [':module']
    ),

    'in_active' => ResponseMessage::make(
        200,
        ':module in active successfully.',
        [':module' => 'Record'],
        [':module']
    ),

    'already_marked' => ResponseMessage::make(
        422,
        ':module already mark as :status.',
        [':module' => 'Record', ':status' => 'current'],
        [':module', ':status']
    ),

    'marked_as' => ResponseMessage::make(
        200,
        ':module mark as :status successfully.',
        [':module' => 'Record', ':status' => 'updated'],
        [':module', ':status']
    ),

    'cannot_mark' => ResponseMessage::make(
        200,
        ':module can not be mark as :status.',
        [':module' => 'Record', ':status' => 'requested'],
        [':module', ':status']
    ),

    'query_error' => ResponseMessage::make(
        501,
        ':message',
        [':module' => 'Record', ':message' => 'Query error.'],
        [':module', ':message']
    ),
];
