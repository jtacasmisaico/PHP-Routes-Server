{ TABLE type1 row size = 206 number of columns = 50 index size = 22 }
create table type1 
  (
    rt smallint,
    version smallint,
    tlid integer,
    side1 smallint,
    source char(1),
    fedirp char(2),
    fename char(30),
    fetype char(4),
    fedirs char(2),
    cfcc char(3),
    fraddl char(11),
    toaddl char(11),
    fraddr char(11),
    toaddr char(11),
    friaddfl smallint,
    toiaddfl smallint,
    friaddfr smallint,
    toiaddfr smallint,
    zipl integer,
    zipr integer,
    fairl integer,
    airr integer,
    anrcl smallint,
    anrcr smallint,
    statel smallint,
    stater smallint,
    countyl smallint,
    countyr smallint,
    fmcdl integer,
    fmcdr integer,
    fsmcdl integer,
    fsmcdr integer,
    fpll integer,
    fplr integer,
    ctbnal integer,
    ctbnal_bn integer,
    ctbnal_suf smallint,
    ctbnar integer,
    ctbnar_bn integer,
    ctbnar_suf smallint,
    blkl char(4),
    blkl_cbn smallint,
    blkl_ts char(1),
    blkr char(4),
    blkr_cbn smallint,
    blkr_ts char(1),
    frlong integer,
    frlat integer,
    tolong integer,
    tolat integer
  );
revoke all on type1 from "public";

create index i_tlid on type1 (tlid);
create index ix109_10 on type1 (cfcc);



