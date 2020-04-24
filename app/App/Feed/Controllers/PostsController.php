<?php

namespace App\Feed\Controllers;

use App\Console\Jobs\PostViewedJob;
use App\Feed\Queries\AllPostsQuery;
use App\Feed\Queries\LatestPostsQuery;
use App\Feed\Queries\TopPostsQuery;
use App\Feed\Requests\PostIndexRequest;
use App\Feed\ViewModels\PostsViewModel;
use App\User\ViewModels\UserInterestsViewModel;
use Domain\Post\Actions\AddViewAction;
use Domain\Post\Models\Post;
use Domain\Post\Models\Tag;
use Domain\Post\Models\Topic;
use Domain\Source\Models\Source;
use Illuminate\Http\Request;
use Spatie\QueryString\QueryString;

final class PostsController
{
    public function index(
        PostIndexRequest $request,
        TopPostsQuery $query
    ) {
        $posts = $query->paginate(5);

        $posts->appends($request->except('page'));

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($request->getTopicSlug())
            ->withTagSlug($request->getTagSlug())
            ->withTitle(__('All'))
            ->view('home.index');

        return $viewModel;
    }

    public function all(
        PostIndexRequest $request,
        AllPostsQuery $query
    ) {
        /** @var \Domain\User\Models\User|null $user */
        $user = $request->user();

        if ($user && $user->interests->isEmpty()) {
            $viewModel = new UserInterestsViewModel($user);

            return $viewModel->view('posts.noInterests');
        }

        $posts = $query->paginate();

        $posts->appends($request->except('page'));

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($request->getTopicSlug())
            ->withTagSlug($request->getTagSlug())
            ->withTitle(__('Discover'))
            ->view('posts.index');

        return $viewModel;
    }

    public function latest(
        PostIndexRequest $request,
        LatestPostsQuery $query
    ) {
        $posts = $query->paginate();

        $posts->appends($request->except('page'));

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($request->getTopicSlug())
            ->withTagSlug($request->getTagSlug())
            ->withTitle(__('Latest'))
            ->view('posts.index');

        return $viewModel;
    }

    public function top(
        PostIndexRequest $request,
        TopPostsQuery $query
    ) {
        $posts = $query->paginate();

        $posts->appends($request->except('page'));

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($request->getTopicSlug())
            ->withTagSlug($request->getTagSlug())
            ->withTitle(__('Top this week'))
            ->view('posts.index');

        return $viewModel;
    }

    public function topic(
        PostIndexRequest $request,
        AllPostsQuery $query,
        Topic $topic
    ) {
        $posts = $query->whereTopic($topic)->paginate();

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($topic->slug)
            ->withTagSlug($request->getTagSlug())
            ->view('posts.index');

        return $viewModel;
    }

    public function tag(
        PostIndexRequest $request,
        AllPostsQuery $query,
        Tag $tag
    ) {
        $posts = $query->whereTag($tag)->paginate();

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withTopicSlug($tag->topic->slug)
            ->withTagSlug($tag->slug)
            ->view('posts.index');

        return $viewModel;
    }

    public function source(
        PostIndexRequest $request,
        AllPostsQuery $query,
        Source $sourceByWebsite
    ) {
        $posts = $query->whereSource($sourceByWebsite)->paginate();

        $viewModel = (new PostsViewModel($posts, $request->user()))
            ->withSourceWebsite($sourceByWebsite->website)
            ->withTagSlug($request->getTagSlug())
            ->withTopicSlug($request->getTopicSlug())
            ->view('posts.index');

        return $viewModel;
    }

    public function show(
        Request $request,
        Post $post,
        AddViewAction $addViewAction
    ) {
        dispatch(new PostViewedJob(
            $addViewAction,
            $post,
            $request->user()
        ));

        $queryString = (new QueryString($post->getFullUrl()))->enable('ref', 'aggregate.stitcher.io');

        return redirect()->to((string) $queryString);
    }
}
