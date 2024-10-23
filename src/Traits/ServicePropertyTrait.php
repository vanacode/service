<?php

namespace Vanacode\Service\Traits;

use Vanacode\Service\Service;
use Vanacode\Support\Exceptions\DynamicClassPropertyException;

trait ServicePropertyTrait
{
    protected Service $service;

    protected bool $overSetService = true;

    /**
     * if $service argument is null based $serviceClass argument or serviceClass() method dynamically make service
     * then set $service property
     * if $overSetService property is true then reset service $model and $validator property by dynamic model, validator match,
     * reset validator $model property as well
     *
     * if $overSetService property is false and $model property is not set yet in $service property,
     * then set service $model property by dynamic model match
     * if service $validator is default validator instance just recreate it and set
     * if validator $model property is empty set service $model property to service validator $model property
     *
     * @throws DynamicClassPropertyException
     */
    public function initializeService(?Service $service = null, string $serviceClass = '', array $data = []): self
    {
        if ($this->overSetService) {
            return $this->initializeServiceAndOverSet($service, $serviceClass, $data);
        }
        $this->setServiceBy($service, $serviceClass, $data);

        if (! $this->service->isSetModel()) {
            $modelClass = $data['model_class'] ?? $this->serviceModelClass();
            $model = $this->getModelBy($modelClass, $data['model_data'] ?? []);
            if ($model) {
                $this->service->setModel($model);
            }
        }

        $validatorClass = $data['validator_class'] ?? $this->serviceValidatorClass();
        $validator = $this->service->getValidator();
        if (get_class($validator) == $validatorClass) {
            $validator = $this->makeValidator($validatorClass, $data['validator_data'] ?? []);
            if (get_class($validator) != $validatorClass) {
                $this->service->setValidator($validator);
            }
        }

        if (! $validator->isSetModel() && $this->service->isSetModel()) {
            $validator->setModel($this->service->getModel());
        }

        return $this;
    }

    /**
     * if $service argument is null based $serviceClass argument or serviceClass() method dynamically make service
     * then set $service property
     * if $overSetService property is true then reset service $model and $validator property by dynamic model, validator match,
     * reset validator $model property as well
     *
     * @throws DynamicClassPropertyException
     */
    public function initializeServiceAndOverSet(?Service $service = null, string $serviceClass = '', array $data = []): self
    {
        $this->setServiceBy($service, $serviceClass, $data);
        $validatorClass = $data['validator_class'] ?? $this->serviceValidatorClass();
        $validator = $this->makeValidator($validatorClass, $data['validator_data'] ?? []);
        $this->service->setValidator($validator);

        $modelClass = $data['model_class'] ?? $this->serviceModelClass();
        $model = $this->getModelBy($modelClass, $data['model_data'] ?? []);

        if ($model) {
            $this->service->setModel($model);
            $this->service->getValidator()->setModel($model);
        }

        return $this;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * if $service argument is null based $serviceClass argument or serviceClass() method dynamically make service
     * then set $service property
     *
     * @throws DynamicClassPropertyException
     */
    public function setServiceBy(?Service $service, string $serviceClass = '', array $data = []): self
    {
        if (is_null($service)) {
            $service = $this->makeService($serviceClass, $data);
        }

        return $this->setService($service);
    }

    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * based $serviceClass argument or serviceClass() method dynamically make service
     *
     * @throws DynamicClassPropertyException
     */
    public function makeService(string $serviceClass = '', array $data = []): Service
    {
        $serviceClass = $serviceClass ?: $this->serviceClass();
        if (! array_key_exists('default', $data)) {
            $data['default'] = $serviceClass;
        }

        return $this->makePropertyInstance('service', $serviceClass, 'Services', 'Service', $data);
    }

    public function serviceClass(): string
    {
        return Service::class;
    }

    public function serviceModelClass(): string
    {
        return $this->service->modelClass();
    }

    public function serviceValidatorClass(): string
    {
        return $this->service->validatorClass();
    }
}
