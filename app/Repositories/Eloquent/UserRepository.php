<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Contracts\IUser;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Repositories\Eloquent\BaseRepository;

class UserRepository extends BaseRepository implements IUser
{
    public function model()
    {
        return User::class;  // === return 'App\Models\User'
    }

    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function search(Request $request)
    {
        $query = (new $this->model)->newQuery();

        // only designers with designs
        if ($request->has_designs) {
            $query->has('designs');
        }

        // check for available to hire
        if ($request->available_to_hire) {
            $query->where('available_to_hire', true);
        }

        // geographic search
        $dist = $request->distance;
        $unit = $request->unit;

        if ($request->lat && $request->lng) {
            // convert lat and lng to point object
            $point = new Point($request->lat, $request->lng);

            // convert dist from km and miles to metres
            $unit == 'km' ? $dist *= 1000 : $dist *= 1609.34;

            $query->distanceSphereExcludingSelf('location', $point, $dist);
        }

        // order result
        if ($request->order === 'closest') {
            $query->orderByDistanceSphere('location', $point, 'asc');
        } else if ($request->order === 'latest') {
            $query->latest();
        } else {
            $query->oldest();
        }

        return $query->get();
    }
}
