<?php

namespace App\Http\Controllers;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

abstract class RepositoryController extends Controller
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Constructor
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return static
            ::getResource()
            ::collection($this
                ->repository
                ->getItems());
    }

    /**
     * @return string
     */
    abstract public static function getResource(): string;
}
