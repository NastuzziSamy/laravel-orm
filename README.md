
# Laravel ORM
Describe your table fields in the model and let Laravel manage your migration for you.


# Installation
## Simple installation
`composer require laravel-orm/laravel-orm`

# Usage
Laravel ORM allows you to automate multiple Laravel features as:
    - Describe perfectly all fields and relations for your model
    - Manage with the right type all your model attributes
    - Create easely your relations
    - Generate for you all your migrations (depending on your model description)
    - Create all scopes and helpers for building queries
    - Smartly add validations to your controller


## Model description
In a regular `User` model, it is hard to detect all the fields, the relations, without reading deeply the code or the migration file(s).

### Before
```php
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $fillable = ['firstname', 'lastname', 'email', 'admin', 'group_id'];

    protected $casts = [
        'admin' => 'boolean',
    ];

    // By default, we want all except the admin boolean
    protected $hidden = ['admin'];

    public function group() {
        $this->belongsTo(Group::class);
    }
}
```

### After
```php
use LaravelORM\Fields\{
    IncrementField, StringField, EmailField, BooleanField
};
use LaravelORM\CompositeFields\BelongsField;
use LaravelORM\Model;

class User extends Model {
    protected function __schema($schema, $fields) {
        /* Increment fields are by default unfillable, not null,  not equal to 0, visible, positive and integer */
        $fields->id = IncrementField::class;
        /* String fields are by default fillable, not null and not blank */
        $fields->firstname = StringField::class;
        $fields->lastname = StringField::class;
        /* Email fields are by default like string fields */
        $fields->email = EmailField::new()->unique();
        /* Boolean fields are by default fillable, not null and visible */
        $fields->admin = BooleanField::new()->default(false)->hidden();
        $fields->group = BelongsField::new()->to(Group::class);

        /* Generate all timestamps */
        $schema->timestamps();

        /* Set first and lastname as unique */
        $schema->unique([$fields->firstname, $fields->lastname]);
    }
}
```

Here we can now know all defined fields. The `group` relation is automatically defined.


## Migrations
### Before

Part of the migration file:
```php
Schema::create('users', function (Blueprint $table) {
    $table->increments('id');
    $table->string('firstname');
    $table->string('lastname');
    $table->string('email')->unique();
    $table->integer('group_id')->unsigned(true);
    $table->timestamp('created_at')->nullable(true);
    $table->timestamp('updated_at')->nullable(true);

    $table->foreign('group_id')->references('id')->on('users');

    $table->unique('firstname', 'lastname');
});
```

### After
The previous file is generated via your model description.


## Model interaction
### Before
```php
	// Get the first user
	$user = User::first();
	// Get with id 2
	User::where('id', 2)->first();
    // Set user with id 3
    $user->id = 3;
	// Get the email adress
	$user->email;
	// Set a bad format email adress
	$user->email = 'bad-email';
	// Set a good format email adress
	$user->email = 'good@email.orm';
    // Set the group_id with a string
    $user->group_id = "2"; // "2"
    // Set the group_id with a negative value
    $user->group_id = -5; // -5
    // Set the group_id with null
    $user->group_id = null; // null
    // Get users with the same lastname
    User::where('lastname', $user->lastname)->get();
	// Get user group relation
	$user->group();
	// Get user group
	$group = $user->group;
	// Search one user by group
	$user = $user->where('group_id', $group->id)->first();
	// Search all users by group
	$user = $user->where('group_id', $group->id)->get();
	// Search all users with a group id greater than specific one
	$user = $user->where('group_id', '>', $group->id)->get();
	// Get admin boolean (with casting)
	$user->admin; // true
	// Get admin boolean (without casting)
	$user->admin; // 1
```

### After
```php
	// Get the first user
	$user = User::first();
	// Get with id 2
	$user2 = User::id(2);
    // Set user with id 3
    $user->id = 3; // Excepts because id is not fillable
	// Get the email adress
	$user->email;
	/* Set a bad format email adress
	 * In function of setttings:
	 * - Add automatically the domain (ex: '@email.orm')
	 * - Write the wrong email
	 * - Throw an exception telling the email adress is wrong
	 */
	$user->email = 'bad-email'; // => bad-email@email.orm or Exception
	// Set a good format email adress
	$user->email = 'good@email.orm';
    // Set the group_id with a string
    $user->group_id = "2"; // 2 (auto casting)
    // Set the group_id with a negative value
    $user->group_id = -5; // Excepts, cannot be negative
    // Set the group_id with null
    $user->group_id = null; // Excepts, cannot be null
    // Get users with the same lastname
    $user->lastname()->get(); // Calling the field will automatically create a query with the field key and value
	// Get user group relation
	$user->group();
	// Get user group
	$group = $user->group;
	// Search user by group
	$user = $user->group($group);
	// Search user by group
	$user = $user->whereGroup($group)->get();
	// Search all users with a group id greater than specific one
	$user = $user->whereGroup('>', $group)->get();
	// Get admin boolean (auto casting)
	$user->admin; // true
```


## Validations
### Not yet done
