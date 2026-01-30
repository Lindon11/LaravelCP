<?php

use App\Modules\Forum\ForumModule;

return [
    /**
     * Triggered when a new forum topic is created
     */
    'OnTopicCreated' => function ($data) {
        // Log the topic creation
        \Log::info('Forum topic created', [
            'topic_id' => $data['topic']->id,
            'author' => $data['author']->username,
            'category' => $data['category']->name,
        ]);
        
        return $data;
    },
    
    /**
     * Triggered when a reply is posted to a topic
     */
    'OnReplyPosted' => function ($data) {
        // Log the reply
        \Log::info('Forum reply posted', [
            'post_id' => $data['post']->id,
            'author' => $data['author']->username,
            'topic_id' => $data['topic']->id,
        ]);
        
        return $data;
    },
    
    /**
     * Alter forum data before display
     */
    'alterForumCategories' => function ($data) {
        // Can be used to modify category display
        return $data;
    },
    
    'alterForumTopics' => function ($data) {
        // Can be used to modify topic display
        return $data;
    },
];
