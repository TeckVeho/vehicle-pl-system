# Issue #468: Breakdown Summary

## Parent Issue

**Issue #468**: Add option to exclude movies from auto-loop delivery  
**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/468

---

## Breakdown Strategy

**Approach**: Default Strategy - 1 Backend Issue + 1 Frontend Issue

**Rationale**:
- Clear ownership per layer (1 BE developer + 1 FE developer)
- Simplified dependency management
- Natural parallel development
- Comprehensive testing within each layer
- Reduced coordination overhead

---

## Created Issues

### 1. Backend Issue

**Issue #469**: [BE] ムービー自動ループ配信除外オプション: データベース・API・ロジック実装 / Loại trừ video khỏi phát sóng tự động: Triển khai Database, API và Logic

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/469

**Story Points**: 4 SP (~4 hours)

**Labels**: `backend`, `enhancement`

**Scope**:
- Database migration: Add `is_loop_enabled` column to `movies` table
- Model update: Update `Movies` model (`$fillable`, `$casts`)
- API endpoint: `PUT /api/movies/{id}/loop-enabled`
- Repository & Interface: Add `updateLoopEnabled` method
- Auto schedule logic: Update `AutoStoreMovieSchedule` command to filter movies
- Request validation: Add validation rules
- Routes: Add new API route
- Backend unit tests

**Files to Edit**:
- `database/migrations/YYYY_MM_DD_HHMMSS_add_is_loop_enabled_to_movies_table.php` (new)
- `app/Models/Movies.php`
- `app/Console/Commands/AutoStoreMovieSchedule.php`
- `app/Http/Controllers/Api/MoviesController.php`
- `app/Repositories/MoviesRepository.php`
- `app/Repositories/Contracts/MoviesRepositoryInterface.php`
- `app/Http/Requests/MoviesRequest.php`
- `routes/api.php`

**Dependencies**: None (can be developed independently)

---

### 2. Frontend Issue

**Issue #470**: [FE] ムービー自動ループ配信除外オプション: トグルボタンUI実装 / Loại trừ video khỏi phát sóng tự động: Triển khai UI nút chuyển đổi

**URL**: https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/470

**Story Points**: 4 SP (~4 hours)

**Labels**: `frontend`, `enhancement`

**Scope**:
- Toggle button UI: Add switch button below movie thumbnails
- State management: Bind toggle state to `item.is_loop_enabled`
- API integration: Implement `handleToggleLoopEnabled` event handler
- Styling: Add SCSS styles for toggle button (ON=green, OFF=gray)
- User experience: Loading overlay, toast notifications, optimistic updates
- Frontend unit tests

**Files to Edit**:
- `resources/js/pages/VideoPlayer/index.vue`
- `resources/js/api/url_api_list.js` (verification only)

**Dependencies**: Backend Issue #469 (for integration testing)

---

## Total Effort Estimation

**Backend**: 4 SP (~4 hours)
**Frontend**: 4 SP (~4 hours)

**Total**: 8 SP (~8 hours)

---

## Implementation Order

### Phase 1: Backend Development (Independent)
**Assignee**: Backend Developer  
**Issue**: #469  
**Duration**: ~4 hours

1. Create database migration
2. Run migration and verify schema
3. Update Movies model
4. Implement Repository & Interface
5. Create API endpoint in Controller
6. Add validation rules
7. Add route
8. Update AutoStoreMovieSchedule command
9. Write backend unit tests
10. Test API endpoint manually

### Phase 2: Frontend Development (Parallel with Backend)
**Assignee**: Frontend Developer  
**Issue**: #470  
**Duration**: ~4 hours

1. Add toggle button UI to VideoPlayer component
2. Implement event handler `handleToggleLoopEnabled`
3. Add API integration
4. Add styling (SCSS)
5. Implement loading states and error handling
6. Add toast notifications
7. Write frontend unit tests
8. Manual UI testing

### Phase 3: Integration Testing (After Both Complete)
**Duration**: ~1-2 hours

1. Test toggle button with real API
2. Verify auto-schedule logic excludes disabled movies
3. Test default behavior (all movies enabled by default)
4. Test edge cases (all movies disabled, etc.)
5. Cross-browser testing

---

## Acceptance Criteria Summary

### Backend (#469)
- [ ] Migration runs successfully and `is_loop_enabled` column is added
- [ ] All existing movies have default value `is_loop_enabled = true`
- [ ] API endpoint `PUT /api/movies/{id}/loop-enabled` works correctly
- [ ] Movies with `is_loop_enabled = false` are excluded from auto-schedule
- [ ] Only movies with `is_loop_enabled = true` are included in auto-schedule
- [ ] Backend unit tests pass
- [ ] No breaking changes to existing functionality

### Frontend (#470)
- [ ] Toggle button displays below each movie thumbnail
- [ ] Default state is ON (green)
- [ ] User can click toggle to switch ON/OFF
- [ ] Toggle state is saved via API
- [ ] Success toast message displays on successful update
- [ ] Error toast message displays on failed update
- [ ] Loading overlay displays during API call
- [ ] Toggle button styling is correct (ON=green, OFF=gray)
- [ ] Frontend unit tests pass
- [ ] No breaking changes to existing functionality

---

## Technical Notes

### API Endpoint
```
PUT /api/movies/{id}/loop-enabled
Body: { "is_loop_enabled": true/false }
Response: { "code": 200, "data": {...} }
```

### Database Schema Change
```sql
ALTER TABLE movies 
ADD COLUMN is_loop_enabled BOOLEAN DEFAULT TRUE 
AFTER position 
COMMENT 'Loop delivery enabled flag: true=enabled, false=disabled';
```

### Auto Schedule Query Update
```php
$movie = Movies::query()
    ->where('id', '>', $subMovieSchedules->movie_id)
    ->where('is_loop_enabled', true)
    ->orderBy('id', 'ASC')
    ->first();
```

---

## Risk Mitigation

### Risk 1: All movies disabled
**Mitigation**: AutoStoreMovieSchedule command logs warning when no loop-enabled movies found

### Risk 2: Migration failure
**Mitigation**: 
- Test migration in staging environment first
- Backup database before production migration
- Rollback procedure prepared

### Risk 3: API integration issues
**Mitigation**:
- Frontend implements error handling and rollback
- Backend validates all inputs
- Comprehensive unit tests for both layers

---

## Story Points Registration

**Backend Issue #469**: ✅ SP = 4 registered to GitHub Projects  
**Frontend Issue #470**: ✅ SP = 4 registered to GitHub Projects

**Registration Method**: Using `.cursor/script/setsp.ps` script

**Verification**:
```bash
# Backend
bash .cursor/script/setsp.ps "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/469" 4

# Frontend
bash .cursor/script/setsp.ps "https://github.com/TeckVeho/Izumi_Issue-Requests-Repo/issues/470" 4
```

---

## Next Steps

1. **Backend Developer**: Start working on Issue #469
   - Command: `/dev 469 --parent 468`

2. **Frontend Developer**: Start working on Issue #470 (can start in parallel)
   - Command: `/dev 470 --parent 468`

3. **After Both Complete**: Integration testing
   - Command: `/test 468`

4. **Create Pull Request**: After all tests pass
   - Command: `/pr 468`

---

**Generated by**: Cursor AI Agent  
**Generated at**: 2025-11-28  
**Branch**: 468-feat-movie-auto-loop-exclusion  
**Total Issues Created**: 2 (1 BE + 1 FE)  
**Total Story Points**: 8 SP

