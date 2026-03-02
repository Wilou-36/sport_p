<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $defaultLanguage = 'fr';

    #[ORM\Column(nullable: true)]
    private ?int $sessionTimeout = 60;

    #[ORM\Column]
    private bool $forcePasswordChange = false;

    #[ORM\Column]
    private bool $twoFactor = false;

    #[ORM\Column]
    private bool $notifyNewBooking = true;

    #[ORM\Column]
    private bool $notifyCancel = true;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $theme = 'light';

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $primaryColor = '#007bff';

    // GETTERS & SETTERS
}