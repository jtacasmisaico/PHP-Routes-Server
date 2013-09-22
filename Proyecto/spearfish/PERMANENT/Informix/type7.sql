{ TABLE type7 row size = 64 number of columns = 13 index size = 0 }
create table type7 
  (
    rt char(1),
    version integer,
    state smallint,
    county smallint,
    land integer,
    source char(1),
    cfcc char(3),
    laname char(30),
    long integer,
    lat integer,
    filler char(1),
    utm_e integer,
    utm_n integer
  );
revoke all on type7 from "public";




