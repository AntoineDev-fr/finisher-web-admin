<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Race
{
    public static function all(): array
    {
        $pdo = Database::get();
        $stmt = $pdo->query('SELECT * FROM races ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function search(string $q): array
    {
        $pdo = Database::get();
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare(
            'SELECT * FROM races WHERE nom LIKE :q OR contact_email LIKE :q ORDER BY created_at DESC'
        );
        $stmt->execute(['q' => $like]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare('SELECT * FROM races WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $race = $stmt->fetch();
        return $race ?: null;
    }

    public static function create(array $data): array
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'INSERT INTO races (nom, description, date_event, prix, latitude, longitude, contact_nom, contact_email, photo_path, created_at, updated_at)
             VALUES (:nom, :description, :date_event, :prix, :latitude, :longitude, :contact_nom, :contact_email, :photo_path, :created_at, :updated_at)'
        );

        $stmt->execute([
            'nom' => $data['nom'],
            'description' => $data['description'],
            'date_event' => $data['date_event'],
            'prix' => $data['prix'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'contact_nom' => $data['contact_nom'],
            'contact_email' => $data['contact_email'],
            'photo_path' => $data['photo_path'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);

        $id = (int)$pdo->lastInsertId();
        return self::find($id) ?? [];
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'UPDATE races SET
                nom = :nom,
                description = :description,
                date_event = :date_event,
                prix = :prix,
                latitude = :latitude,
                longitude = :longitude,
                contact_nom = :contact_nom,
                contact_email = :contact_email,
                photo_path = :photo_path,
                updated_at = :updated_at
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'description' => $data['description'],
            'date_event' => $data['date_event'],
            'prix' => $data['prix'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'contact_nom' => $data['contact_nom'],
            'contact_email' => $data['contact_email'],
            'photo_path' => $data['photo_path'],
            'updated_at' => $data['updated_at'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare('DELETE FROM races WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
