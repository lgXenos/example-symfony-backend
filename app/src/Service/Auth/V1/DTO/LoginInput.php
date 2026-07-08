<?php

declare(strict_types=1);

namespace App\Service\Auth\V1\DTO;

use App\Service\Auth\AuthInputInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Класс LoginInput представляет собой структуру данных (DTO) для входящего запроса авторизации.
 */
final readonly class LoginInput implements AuthInputInterface
{
    /**
     * Конструктор с правилами валидации свойств
     *
     * @param string $login Логин пользователя. Не должен быть пустым, длина от 3 до 20 символов.
     * @param string $password Пароль пользователя. Не должен быть пустым.
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Login cannot be blank.')]
        #[Assert\Length(
            min: 3,
            max: 20,
            minMessage: 'Login must be at least {{ limit }} characters long.',
            maxMessage: 'Login cannot be longer than {{ limit }} characters.'
        )]
        public string $login,

        #[Assert\NotBlank(message: 'Password cannot be blank.')]
        public string $password,
    ) {
    }
}
