<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Article;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticlesTest extends TestCase
{
    use RefreshDatabase;

    //--- store

    /**
     * @test
     */
    public function unauthenticated_user_cannot_store_article()
    {
        $this->json('POST', route('articles.store'))
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function authenticated_user_cannot_store_article_with_empty_data()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $this->json('POST', route('articles.store'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'text', 'publish']);
    }


    /**
     * @test
     */
    public function authenticated_user_can_store_article()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $data = factory(Article::class)->make()->toArray();

        $this->assertDatabaseMissing('articles', [
            'user_id' => $user->id,
            'title' => $data['title'],
        ]);

        $this->json('POST', route('articles.store', $data))
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'title' => $data['title'],
        ]);
    }

    //--- index

    /**
     * @test
     */
    public function user_can_view_articles_overview()
    {
        $article = factory(Article::class)->create();
        $hiddenArticle = factory(Article::class)->create(['publish' => false]);

       // dd($this->json('GET', route('articles.index'))->decodeResponseJson());
        $this->json('GET', route('articles.index'))
                ->assertStatus(200)
                ->assertJsonStructure(['data' => [['id', 'title', 'updated_at', 'created_at', 'user']], 'total'])
                ->assertJsonFragment(['total' => 1])
                ->assertJsonFragment(['id' => $article->id])
                ->assertJsonMissing(['id' => $hiddenArticle->id]);
    }

    /**
     * @test
     */
    public function unauthenticated_user_cannot_view_unpublished_articles_overview()
    {
        $article = factory(Article::class)->create();
        $hiddenArticle = factory(Article::class)->create(['publish' => false]);

        $this->json('GET', route('articles.index', ['publish' => 0]))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'updated_at', 'created_at', 'user']], 'total'])
            ->assertJsonFragment(['total' => 1])
            ->assertJsonFragment(['id' => $article->id])
            ->assertJsonMissing(['id' => $hiddenArticle->id]);
    }

    /**
     * @test
     */
    public function authenticated_user_can_view_unpublished_articles_overview()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $article = factory(Article::class)->create();
        $hiddenArticle = factory(Article::class)->create(['publish' => false]);

        $this->json('GET', route('articles.index', ['publish' => 0]))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'title', 'updated_at', 'created_at', 'user']], 'total'])
            ->assertJsonFragment(['total' => 1])
            ->assertJsonFragment(['id' => $hiddenArticle->id])
            ->assertJsonMissing(['id' => $article->id]);
    }
}
