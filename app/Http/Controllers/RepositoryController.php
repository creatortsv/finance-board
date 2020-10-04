<?php

namespace App\Http\Controllers;

use App\Repositories\RepositoryInterface;

class RepositoryController extends Controller
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
}
