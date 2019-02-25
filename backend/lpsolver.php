<?php

$solver = <<<EOT
#include <incmode>.

#const dim=6.
#const goalx=5.
#const goaly=3.
#const goalcar=red.

#const imin=2.
#const imax=100.

#program base.

#program step(k).

1 { do(k,move(Id,1..Lim)) :
    h(k-1,car(Id,X,Y,Dir,Len)),
    Lim=dim+1-Len
  }.

#minimize { 1 : do(_, _) }.

h(k,at(Id,X1,Y)) :- h(k-1,car(Id,X,Y,x,Len)),
                    X1 = X..X+Len-1.

h(k,at(Id,X,Y1)) :- h(k-1,car(Id,X,Y,y,Len)),
                    Y1 = Y..Y+Len-1.

h(k,at(Id,X1,Y)) :- h(k-1,car(Id,X,Y,x,Len)),
                    X1 = X..X+Len-1.

h(k,at(Id,X,Y1)) :- h(k-1,car(Id,X,Y,y,Len)),
                    Y1 = Y..Y+Len-1.

h(k,at(Id,MinX..MaxX+Len-1,Y)) :- h(k-1,car(Id,X,Y,x,Len)),
                                  do(k,move(Id,Dest)),
                                  MinX = #min { X0:X0=X ; D0:D0=Dest },
                                  MaxX = #max { X0:X0=X ; D0:D0=Dest }.

h(k,at(Id,X,MinY..MaxY+Len-1)) :- h(k-1,car(Id,X,Y,y,Len)),
                                  do(k,move(Id,Dest)),
                                  MinY = #min { Y0:Y0=Y ; D0:D0=Dest },
                                  MaxY = #max { Y0:Y0=Y ; D0:D0=Dest }.

% don't move 0
:- do(k, move(Id,Dest)),
   h(k-1,car(Id,From,_,x,_)),
   |From-Dest| != 1.

:- do(k, move(Id,Dest)),
   h(k-1,car(Id,_,From,y,_)),
   |From-Dest| != 1.   

% invalid position
:- h(k,at(Id1,X,Y)),
   h(k,at(Id2,X,Y)),
   Id2 != Id1.

:- do(k,move(Id,Dest1)), do(k, move(Id,Dest2)), Dest1 != Dest2.

h(k,car(Id,X,Y,Dir,Len)) :- h(k-1,car(Id,X,Y,Dir,Len)),
                            not do(k, move(Id,_)).

h(k,car(Id,Dest,Y,x,Len)) :- do(k, move(Id,Dest)),
                             h(k-1,car(Id,_,Y,x,Len)).

h(k,car(Id,X,Dest,y,Len)) :- do(k, move(Id,Dest)),
                             h(k-1,car(Id,X,_,y,Len)).

#program check(k).

:- query(k), not h(k, car(goalcar, goalx, goaly, _, _)).

#show do/2.

EOT;

?>
