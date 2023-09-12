<?php

namespace App\DataFixtures;

use App\Entity\User;
use Faker\Factory;
use App\Entity\Comment;
use App\Entity\BlogPost;
use App\Repository\UserRepository;
use App\Repository\BlogPostRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class AppFixtures extends Fixture
{
    
    private $userPasswordHasher;

    private $userRepository;  

    private $blogPostRepository;  

    /**
     * @var \Faker\Factory $faker
     */
    private $faker;

    private const USERS = [
        [
            'username' => 'john_doe',
            'email' => 'john_doe@doe.com',
            'password' => 'john123',
            'name' => 'John Doe',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob_smith@smith.com',
            'password' => 'rob12345',
            'name' => 'Rob Smith',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'marry_gold',
            'email' => 'marry_gold@gold.com',
            'password' => 'marry12345',
            'name' => 'Marry Gold',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'jane_shepard',
            'email' => 'jane_shepard@shepard.com',
            'password' => 'jane12345',
            'name' => 'Commander Shepard',
            'roles' => [User::ROLE_USER]
        ],        
        [
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin12345',
            'name' => 'Admin',
            'roles' => [User::ROLE_ADMIN]
        ],        
    ];  
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, BlogPostRepository $blogPostRepository) 
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->blogPostRepository = $blogPostRepository;
        $this->faker = \Faker\Factory::create();
    }     

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadPosts($manager);   
        $this->loadComments($manager);  
    }
 
      
    public function loadUsers(ObjectManager $manager): void
    {      
        foreach (self::USERS as $userData) {
            $user = new User(); 
            $user->setUsername($userData["username"]);
            $user->setEmail($userData["email"]);
            $user->setName($userData["name"]);
            $hashedPassword = $this->userPasswordHasher->hashPassword(
                $user, 
                $userData["password"]
            );
            $user->setPassword($hashedPassword);

            $user->setRoles($userData["roles"]);

            $user->setUuid(Uuid::v4());

            $manager->persist($user);
        }
        $manager->flush();

    }   
    
    public function loadPosts(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $post = new BlogPost();

            $post->setTitle($this->faker->realText(30));
            $post->setContent($this->faker->realText(255));
            $post->setSlug($this->faker->slug(3));
            $date = new \DateTimeImmutable($this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'));
            $post->setCreatedAt($date);
            $post->setIsPublished(true);

            $users = $this->userRepository->findAll();
            $rand_user = $users[rand(0, count($users) - 1)];
            $post->setAuthor($rand_user);

            $manager->persist($post);           
        }
        $manager->flush();
    }   
    
    public function loadComments(ObjectManager $manager): void
    {
        for ($i = 0; $i < 150; $i++) {
            
            $comment = new Comment();

            $date = new \DateTimeImmutable($this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'));

            $comment->setContent($this->faker->realText(255));
            $comment->setCreatedAt($date);
            $comment->setIsPublished(true);

            $users = $this->userRepository->findAll();
            $rand_user = $users[rand(0, count($users) - 1)];
            $comment->setAuthor($rand_user);

            $posts = $this->blogPostRepository->findAll();
            $rand_post = $posts[rand(0, count($posts) - 1)];
            $comment->setPost($rand_post);         

            $manager->persist($comment);           
        }
        $manager->flush();
    }               
}
