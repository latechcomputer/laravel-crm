<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Validator;

class Log extends Model
{
    /** 
     * This models immutable values.
     *
     * @var array 
     */
    protected $guarded = [];

    /**
     * Set a publicily accessible identifier to get the path for this unique instance.
     * 
     * @return  string
     */
    public function getPathAttribute()
    {
        return url('/').'/logs/'.$this->slug;
    }

    /**
     * This model relationship belongs to \App\User.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_created');
    }

    /**
     * This model relationship belongs to \App\Client.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function client()
    {
        return $this->belongsTo('App\Client', 'client_id');
    }

    /**
     * Set a publicily accessible identifier to get the updated by for this unique instance.
     * 
     * @return  string
     */
    public function getUpdatedByAttribute()
    {
        $user = User::where('id', $this->user_modified)->first();

        return $user;
    }

    /**
     * Get validation errors for an operation that creates or updates values of an instance of this model.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function validationErrors($request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:191|min:5',
            'description'  => 'required|min:20',
            'body'  => 'required|min:20'
        ]);

        return $validator->errors()->all();
    }

    /**
     * Gets request data for an operation that creates or updates values of an instance of this model.
     * 
     * @param  \Illuminate\Http\Request  $request
     */
    public function parseData($request)
    {
        $data = array();
        $data['title']       = request('title');
        $data['description'] = request('description');
        $data['body']        = request('body');
        $data['notes']       = request('notes');

        return $data;
    }

    /**
     * Updates values of this instance.
     * 
     * @param  array  $data
     * @return bool
     */
    public function updateLog($data)
    {
        $this->user_modified = auth()->user()->id;
        $this->title         = trim(filter_var($data['title'], FILTER_SANITIZE_STRING));
        $this->description   = trim(filter_var($data['description'], FILTER_SANITIZE_STRING));
        $this->body          = trim(filter_var($data['body'], FILTER_SANITIZE_STRING));
        $this->notes         = trim(filter_var($data['notes'], FILTER_SANITIZE_STRING));

        return ($this->save()) ? TRUE : FALSE;
    }

    /**
     * Set a publicily accessible identifier to get the description for this unique instance.
     * 
     * @return  string
     */
    public function getDescriptionAttribute()
    {
        return nl2br(e($this->attributes['description']));
    }

    /**
     * Set a publicily accessible identifier to get the body for this unique instance.
     * 
     * @return  string
     */
    public function getBodyAttribute()
    {
        return nl2br(e($this->attributes['body']));
    }

    /**
     * Set a publicily accessible identifier to get the notes for this unique instance.
     * 
     * @return  string
     */
    public function getNotesAttribute()
    {
        return nl2br(e($this->attributes['notes']));
    }

    /**
     * Set a publicily accessible identifier to get the edit description for this unique instance.
     * 
     * @return  string
     */
    public function getEditDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return e($desc);
    }

    /**
     * Set a publicily accessible identifier to get the edit body for this unique instance.
     * 
     * @return  string
     */
    public function getEditBodyAttribute()
    {
        $body = str_replace('<br/>', '', $this->attributes['body']);

        return e($body);
    }

    /**
     * Set a publicily accessible identifier to get the edit notes for this unique instance.
     * 
     * @return  string
     */
    public function getEditNotesAttribute()
    {
        $notes = str_replace('<br/>', '', $this->attributes['notes']);

        return e($notes);
    }

    /**
     * Set a publicily accessible identifier to get the short description for this unique instance.
     * 
     * @return  string
     */
    public function getShortDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return strlen($desc) > 300 ? substr($desc,0,299).'...' : $desc;
    }

    /**
     * Adds onto a query parameters provided in request to search for items of this instance.
     * 
     * @param  \Illuminate\Database\Eloquent\Model  $query
     * @param  \Illuminate\Http\Request             $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeSearch($query, $request)
    {
        if($request->has('search'))
        {
            $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);
                    
            if($request->has('title') || $request->has('desc') || $request->has('body') || 
                $request->has('created_at') || $request->has('updated_at'))
            {
                if($request->has('search'))
                {
                    if($request->has('title')) 
                    {
                        $query = $query->where('logs.title', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('desc')) 
                    {
                        $query = $query->where('logs.description', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('body')) 
                    {
                        $query = $query->where('logs.body', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('created_at')) 
                    {
                        $query = $query->whereDate('logs.created_at', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('updated_at')) 
                    {
                        $query = $query->whereDate('logs.updated_at', 'like', '%'.$searchParam.'%');
                    }
                }
            }
            else
            {
                $query = $query->where('logs.title', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.description', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.body', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.created_at', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get logs available to a given user.
     *
     *  @param  \Illuminate\Database\Eloquent\Model  $query
     *  @param \App\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleLogs($query, $user)
    {
        return $query->select(
                'logs.id', 'logs.slug', 'logs.title', 'logs.description', 'logs.created_at', 'logs.updated_at'
            )
            ->leftJoin('client_user', 'logs.client_id', '=', 'client_user.client_id')
            ->where('client_user.user_id', '=', $user->id)
            ->groupBy('logs.id');
    }
}
