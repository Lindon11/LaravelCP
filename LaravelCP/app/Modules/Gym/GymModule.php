<?php

namespace App\Modules\Gym;

use App\Modules\Module;
use App\Models\User;
use App\Services\GymService;

/**
 * Gym Module
 * 
 * Handles gym training system - allows players to train attributes
 */
class GymModule extends Module
{
    protected string $name = 'Gym';
    
    protected GymService $gymService;
    
    public function construct(): void
    {
        $this->gymService = app(GymService::class);
        
        $this->config = [
            'attributes' => ['strength', 'defense', 'speed', 'stamina'],
            'max_per_session' => 100,
        ];
    }
    
    /**
     * Get training info
     */
    public function getTrainingInfo(): array
    {
        $info = $this->gymService->getTrainingInfo();
        
        return $this->applyModuleHook('alterTrainingInfo', $info);
    }
    
    /**
     * Train an attribute
     */
    public function train(User $user, string $attribute, int $times): array
    {
        // Validate attribute
        if (!in_array($attribute, $this->config['attributes'])) {
            return $this->error('Invalid attribute');
        }
        
        // Validate times
        if ($times < 1 || $times > $this->config['max_per_session']) {
            return $this->error('Invalid training amount');
        }
        
        // Apply hooks before training
        $this->applyModuleHook('beforeTrain', [
            'user' => $user,
            'attribute' => $attribute,
            'times' => $times,
        ]);
        
        $result = $this->gymService->train($user, $attribute, $times);
        
        // Apply hooks after training
        $this->applyModuleHook('afterTrain', [
            'user' => $user,
            'attribute' => $attribute,
            'times' => $times,
            'result' => $result,
        ]);
        
        return $result;
    }
}
