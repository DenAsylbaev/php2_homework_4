<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;

use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;


use \PDO;
use \PDOStatement;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    private PDO $connection;
    public function __construct(PDO $connection) 
        {
            $this->connection = $connection;
        }
        
    public function save(Post $post): void
    {        
        $statement = $this->connection->prepare(
            'INSERT INTO posts (post, author, title, txt)
            VALUES (:post, :author, :title, :txt)'
            );

            // Выполняем запрос с конкретными значениями
            $statement->execute([
                ':post' => (string)$post->id(),
                ':author' => (string)$post->getAuthorId(),
                ':title' => $post->getTitle(),
                ':txt' => $post->getText()
            ]);
    }
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE post = ?'
        );
        // $statement->execute([ -- КАК БЫЛО
        //     ':post' => (string)$uuid,
        // ]);
        $statement->execute([(string)$uuid]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $userRepo = new SqliteUsersRepository($this->connection); // чтоб юзера получить потом
        return new Post(
            new UUID($result['post']),
            $userRepo->get(new UUID($result['author'])),
            $result['title'],
            $result['txt']
        );
    }

    public function delete(UUID $uuid)
    {
        print_r('tets');
        print_r((string)$uuid);

        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE post = ?'
        );

        $statement->execute([(string)$uuid]);
    }
}