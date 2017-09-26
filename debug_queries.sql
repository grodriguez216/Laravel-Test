delete from presta_db.payments where id > 0;
delete from presta_db.loans where id > 0;
delete from presta_db.clients where id > 0;

-- Clear all
truncate table payments;