# Booking API

Nowhere near useable yet! Just started on this.

It shall be an api for easily allocating / reserving resources in time.

Watch this space: `    `

(*Alright, you can stop watching now. just come back later.*)


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
