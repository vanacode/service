<?php

namespace Vanacode\Service\Traits;

use Vanacode\Service\Service;

/**
 * use with Vanacode\Support\Traits\DynamicClassTrait
 */
trait ServicePropertyTrait
{
    protected Service $service;

    /**
     * initialize service property
     *
     * make model instance dynamically based caller sub folders first match
     * make folder instance dynamically based caller sub folders first match
     * set model to validator
     * make service instance dynamically based caller sub folders first match with created model and validator arguments
     */
    public function initializeService(?Service $service = null, array $data = []): self
    {
        if ($service) {
            return $this->setService($service);
        }

        $validatorData = $data['validator_data'] ?? [];
        if (! array_key_exists('default', $validatorData)) {
            $validatorData['default'] = static::serviceValidatorClass();
        }

        $model = $this->getModelBy($data['model_data'] ?? []);
        $validator = $this->makeValidator($validatorData);
        if ($model) {
            $validator->setModel($model);
        }

        $data['parameters']['model'] = $model;
        $data['parameters']['validator'] = $validator;
        $this->setServiceBy($service, $data);
        $this->service->setValidator($validator);
        $this->service->setFullResource($this->fullResource); // TODO decide keep or not

        return $this;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Set service property
     *
     * if $service argument is not null
     * otherwise make service instance dynamically based caller sub folders first match and set it
     */
    public function setServiceBy(?Service $service, array $data = []): self
    {
        if (is_null($service)) {
            $service = $this->makeService($data);
        }

        return $this->setService($service);
    }

    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * make service instance dynamically based caller sub folders first match
     */
    public function makeService(array $data = []): Service
    {
        if (! array_key_exists('default', $data)) {
            $data['default'] = static::serviceClass();
        }

        return $this->makeClassDynamically('Services', 'Service', $data);
    }

    public static function serviceClass(): string
    {
        return Service::class;
    }

    public static function serviceValidatorClass(): string
    {
        return static::serviceClass()::validatorClass();
    }
}
