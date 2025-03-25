This is the code for my database scheme, i used "https://dbdiagram.io/d" this website, and saved the image to assets/img/db/
Table users {
  id integer [primary key]
  name varchar
  surname varchar
  licence varchar
  username varchar
  password varchar
  role enum(user,instructor,admin)
  created_at timestamp
}

Table availabilityCalendar{
  id integer [primary key]
  instructor_id integer [not null]
  date date
  status enum(active, not_active)
}


Table services{
  id integer [primary key]
  name varchar
  description varchar
  price int
  valid_from date
  valid_to date
}

Table reviews{
  id integer [primary key]
  booking_id integer [not null]
  grade enum(1,2,3,4,5)
  note varchar
}

Table bookings{
  id integer [primary key]
  user_id integer [not null]
  instructor_id integer
  service_id integer [not null]
  session_type enum(Private_instruction, Ski_school)
  num_of_spots integer
  week enum(week1,week2,week3,week4)
  age_group_child integer
  age_group_teen integer
  age_group_adult integer
  ski_level_b integer
  ski_level_i integer
  ski_level_a integer
  veg_count integer
  other varchar
  date date
  num_of_hours integer
  start_time time
}




Ref: "availabilityCalendar"."instructor_id" > "users"."id"

Ref: "bookings"."user_id" > "users"."id"

Ref: "bookings"."instructor_id" > "users"."id"

Ref: "services"."id" < "bookings"."service_id"

Ref: "bookings"."id" - "reviews"."booking_id"


