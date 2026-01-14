namespace App\Http\Middleware;
use Closure;
use App\Settings\TrainingSettings;
use Illuminate\Http\Request;
use App\Models\User;
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        $settings = app(TrainingSettings::class);
        // 1. Skip if maintenance is OFF
        if (!$settings->is_maintenance_mode) {
            return $next($request);
        }
        // 2. Skip for Admins
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }
        // 3. Check if user role is restricted
        if (auth()->check()) {
            $user = auth()->user();
            if (in_array($user->role, $settings->maintenance_roles ?? [])) {
                return response()->view('errors.maintenance', [
                    'message' => $settings->maintenance_message
                ], 503);
            }
        }
        return $next($request);
    }
}