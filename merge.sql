ATTACH DATABASE 'rcuvss.sqlite3' as chs;
ATTACH DATABASE 'rcuvts.sqlite3' as cht;
ATTACH DATABASE 'niv2011.sqlite3' as niv;
ATTACH DATABASE 'bible.db' as bible;
CREATE TABLE bible.[niv](
    [id] INT PRIMARY KEY ASC NOT NULL UNIQUE, 
    [text] TEXT);
CREATE TABLE bible.[cht](
    [id] INT PRIMARY KEY ASC NOT NULL UNIQUE, 
    [text] TEXT);
CREATE TABLE bible.[chs](
    [id] INT PRIMARY KEY ASC NOT NULL UNIQUE, 
    [text] TEXT);
INSERT INTO bible.niv(id, text) select (b.number * 1000* 1000 + v.verse*1000) as id, unformatted as text from niv.books as b inner join niv.verses as v on b.osis=v.book;
INSERT INTO bible.cht(id, text) select (b.number * 1000* 1000 + v.verse*1000) as id, unformatted as text from cht.books as b inner join cht.verses as v on b.osis=v.book;
INSERT INTO bible.chs(id, text) select (b.number * 1000* 1000 + v.verse*1000) as id, unformatted as text from chs.books as b inner join chs.verses as v on b.osis=v.book;
