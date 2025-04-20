<?php

namespace Luinuxscl\AiPosts\Services;

use Luinuxscl\AiPosts\Exceptions\InvalidStateTransitionException;

class StateMachine
{
    /**
     * Configuración de los estados disponibles.
     *
     * @var array
     */
    protected $states;

    /**
     * Constructor.
     *
     * @param array $states
     */
    public function __construct(array $states)
    {
        $this->states = $states;
    }

    /**
     * Verificar si una transición desde el estado actual al estado objetivo es válida.
     *
     * @param string $currentState
     * @param string $targetState
     * @return bool
     */
    public function canTransition(string $currentState, string $targetState): bool
    {
        if (!isset($this->states[$currentState])) {
            return false;
        }

        return $this->states[$currentState]['next'] === $targetState;
    }

    /**
     * Obtener el siguiente estado disponible para el estado actual.
     *
     * @param string $currentState
     * @return string|null
     */
    public function getNextState(string $currentState): ?string
    {
        if (!isset($this->states[$currentState])) {
            return null;
        }

        return $this->states[$currentState]['next'];
    }

    /**
     * Realizar una transición desde el estado actual al siguiente estado disponible.
     *
     * @param string $currentState
     * @return string|null El nuevo estado o null si no hay siguiente estado
     * @throws InvalidStateTransitionException
     */
    public function transition(string $currentState): ?string
    {
        $nextState = $this->getNextState($currentState);

        if ($nextState === null) {
            return null;
        }

        if (!$this->canTransition($currentState, $nextState)) {
            throw new InvalidStateTransitionException(
                "No se puede hacer la transición desde '{$currentState}' a '{$nextState}'"
            );
        }

        return $nextState;
    }

    /**
     * Verificar si un estado es válido.
     *
     * @param string $state
     * @return bool
     */
    public function isValidState(string $state): bool
    {
        return isset($this->states[$state]);
    }

    /**
     * Obtener el nombre legible de un estado.
     *
     * @param string $state
     * @return string|null
     */
    public function getStateName(string $state): ?string
    {
        if (!$this->isValidState($state)) {
            return null;
        }

        return $this->states[$state]['name'] ?? $state;
    }

    /**
     * Obtener la descripción de un estado.
     *
     * @param string $state
     * @return string|null
     */
    public function getStateDescription(string $state): ?string
    {
        if (!$this->isValidState($state)) {
            return null;
        }

        return $this->states[$state]['description'] ?? null;
    }

    /**
     * Obtener todos los estados disponibles.
     *
     * @return array
     */
    public function getAllStates(): array
    {
        return $this->states;
    }

    /**
     * Obtener todas las transiciones posibles.
     *
     * @return array
     */
    public function getAllTransitions(): array
    {
        $transitions = [];

        foreach ($this->states as $state => $config) {
            if (isset($config['next']) && $config['next'] !== null) {
                $transitions[] = [
                    'from' => $state,
                    'to' => $config['next'],
                    'name' => "De {$this->getStateName($state)} a {$this->getStateName($config['next'])}",
                ];
            }
        }

        return $transitions;
    }
}
