<?php
namespace GeekBrains\LevelTwo\Http\Actions\Comments;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessfulResponse;
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepositories\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepositories\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;

class CreateComment implements ActionInterface
{
    private CommentsRepositoryInterface $commentsRepository;
    private PostsRepositoryInterface $postsRepository;
    private UsersRepositoryInterface $usersRepository;

    // Внедряем репозитории статей и пользователей
    public function __construct(
        CommentsRepositoryInterface $commentsRepository,
        PostsRepositoryInterface $postsRepository,
        UsersRepositoryInterface $usersRepository
    ) {
        $this->commentsRepository = $commentsRepository;
        $this->postsRepository = $postsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function handle(Request $request): Response
    {
        // Пытаемся создать UUID пользователя из данных запроса
        try {
            $authorUuid = new UUID($request->jsonBodyField('author_uuid'));

        } catch (HttpException | InvalidArgumentException $e) {
            print_r('Errr0');

            return new ErrorResponse($e->getMessage());
        }

        // Пытаемся найти пользователя в репозитории
        try {
            $this->usersRepository->get($authorUuid);
        } catch (UserNotFoundException $e) {
            print_r('Errr1');

            return new ErrorResponse($e->getMessage());
        }

        // Пытаемся создать UUID статьи из данных запроса
        try {
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            print_r('Errr2');
            return new ErrorResponse($e->getMessage());
        }

        // Пытаемся найти эту статью в репозитории
        try {
            $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $e) {
            print_r('Errr3');

            return new ErrorResponse($e->getMessage());
        }

        // Генерируем UUID для нового комментария
        $newCommentUuid = UUID::random();

        try {
            // Пытаемся создать объект комментария
            // из данных запроса
            $comment = new Comment(
            $newCommentUuid,
            $this->usersRepository->get($authorUuid),
            $this->postsRepository->get($postUuid),
            $request->jsonBodyField('text'),
            );

        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Сохраняем новую статью в репозитории
        $this->commentsRepository->save($comment);

        // Возвращаем успешный ответ,
        // содержащий UUID новой статьи
        return new SuccessfulResponse([
            'uuid' => (string)$newCommentUuid
        ]);
    }
}