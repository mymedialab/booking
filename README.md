# Booking API

Nowhere near useable yet! Just started on this.

It shall be an api for easily allocating / reserving resources in time.

Watch this space: `    `

(*Alright, you can stop watching now. just come back later.*)

## Setup

 1. Add to composer.json. This is not going on packagist until the API's settled down, so for now use at your own risk
    thusly:

        {
            "repositories": [
                {
                    "type": "git",
                    "url":  "https://github.com/mymedialab/booking.git"
                }
            ],
            "require": {
                "mml/booking": "0.0.1@dev"
            }
        }

 2. Setup your database. **WARNING This could be destructive**. Doctrine is used to manage the database and expects a
    database of its very own. When you run the update **any existing tables will be dropped**. The easiest way to do the
    install is to copy the install script found in `vendor/mml/booking/Utilities/installSchema.php` and modify it with
    your own database details. You'll want to then run that and your DB is all setup.

 3. Integrate into your UI. I find the easiest way to do this is to register a new serviceprovider which creates the
    General Factory, The main App and the Setup model if you need it. From there, those API's should give you all you
    need. For Laravel a provider may look like this:

        <?php
        namespace Acme\Awesomeness;

        use Illuminate\Support\ServiceProvider;

        class BookingServiceProvider extends ServiceProvider
        {
            public function register()
            {
                $this->app->singleton('BookingFactory', function()
                {
                    $dbSettings = array(
                        'mysqlUser'     => 'aUser',
                        'mysqlPassword' => 'some super secret passphrase',
                        'mysqlDatabase' => 'my_own_database',
                        'mysqlHost'     => 'localhost'
                    );

                    return new \MML\Booking\Factories\General($dbSettings);
                });
                $this->app->bind('Booking', function()
                {
                    return new \MML\Booking\App(\App::make('BookingFactory'));
                });
                $this->app->bind('BookingSetup', function()
                {
                    return new \MML\Booking\Setup(\App::make('BookingFactory'));
                });
            }
        }

    For other frameworks, you know what you're about, the above should point you right.

## Caveats and Gotchas

This API relies on period-smoothing. So you'll create a period such as 3pm to 11am for a hotel room and if you ask for
Monday, you'll get a period of 3pm monday to 11am Tuesday. Smoothed. On the whole, this is great. But... here's an
example of a potential in-use issue:

A Board Room is available hourly. User A books 09:00 til 10:00, user B books 11:00 til 12:00 These are stored in the db
to the second, so if user C wants the 10:00 til 11:00 slot we use the query

    available if (start >= A.end OR end <= B.start)

This has a major advantage in that to a human person, the hours are clear. However, we fail hard if you use per-second
periods, as BOTH A and C have the room at 10:00:00 and both B AND C have the room at 11:00:00. We *could* adjust the end
time to be 09:59:59 or adjust the start time to be 10:00:01 and then we could modify the query to

    available if (start > A.end OR end < B.start)

BUT then the API consumer would (probably) need to magically tweak the date / time every time it's presented to the end
user.

## Projects todos.

Lots.

Some specifics are scattered around the code base (`grep todo src` if you want to get a feel for it) other, more general
items are below. These should give you an idea of just how in-development this dev stability project is!

 * Database intereaction: I LOVE DOCTRINE. But you may not. Can we make this not rely so heavily on it? abstract out the
   models etc. Most of them are interface based already, but maybe give a general schema and fit a few other popular
   ORM's out-of-the-box? A good start would be to rename models folder to entities or doctrine persistence or something.

 * More database stuff. This shouldn't require its own DB. Even if we stick with Doctrine, can we somehow use a table
   prefix and a cleverer installer? Would be nice to have a co-habiting system. Maybe need to move away from in-file
   specification?

 * Block booking still not done. (meta: will this comment rot soon? I intend to sort this next!)

 * Database quickly builds up cruft because of the many-to-many's everywhere. I should cascade removals better. Should
   it be optional?

 * The Interval / Reservation meta are basically the same table. Could we put a pivot column on there? Seems a bit
   over-normalised to me, one indirection too far. But it might just make us more portable and easily extensible?

 * This is a biggie: Simplify! A Lot! We should provide well tested convenience wrappers for common types of usage.
